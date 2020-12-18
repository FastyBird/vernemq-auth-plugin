<?php declare(strict_types = 1);

/**
 * VernemqCommand.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:VerneMqAuthPlugin!
 * @subpackage     Commands
 * @since          0.1.0
 *
 * @date           26.06.20
 */

namespace FastyBird\VerneMqAuthPlugin\Commands;

use Contributte\Translation;
use Doctrine\Common;
use Doctrine\DBAL\Connection;
use FastyBird\DevicesModule\Entities as DevicesModuleEntities;
use FastyBird\DevicesModule\Models as DevicesModuleModels;
use FastyBird\DevicesModule\Queries as DevicesModuleQueries;
use FastyBird\VerneMqAuthPlugin\Exceptions;
use FastyBird\VerneMqAuthPlugin\Models;
use FastyBird\VerneMqAuthPlugin\Queries;
use FastyBird\VerneMqAuthPlugin\Types;
use Nette\Utils;
use Psr\Log;
use stdClass;
use Symfony\Component\Console;
use Symfony\Component\Console\Input;
use Symfony\Component\Console\Output;
use Symfony\Component\Console\Style;
use Throwable;

/**
 * Synchronize Verne MQ accounts
 *
 * @package        FastyBird:VerneMqAuthPlugin!
 * @subpackage     Commands
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class SynchroniseCommand extends Console\Command\Command
{

	/** @var DevicesModuleModels\Devices\IDeviceRepository */
	private DevicesModuleModels\Devices\IDeviceRepository $deviceRepository;

	/** @var Models\Accounts\IAccountRepository */
	private Models\Accounts\IAccountRepository $accountRepository;

	/** @var Models\Accounts\IAccountsManager */
	private Models\Accounts\IAccountsManager $accountsManager;

	/** @var Common\Persistence\ManagerRegistry */
	private Common\Persistence\ManagerRegistry $managerRegistry;

	/** @var Log\LoggerInterface */
	private Log\LoggerInterface $logger;

	/** @var Translation\PrefixedTranslator */
	private Translation\PrefixedTranslator $translator;

	/** @var string */
	private string $translationDomain = 'commands.sync';

	public function __construct(
		DevicesModuleModels\Devices\IDeviceRepository $deviceRepository,
		Models\Accounts\IAccountRepository $accountRepository,
		Models\Accounts\IAccountsManager $accountsManager,
		Translation\Translator $translator,
		Common\Persistence\ManagerRegistry $managerRegistry,
		?Log\LoggerInterface $logger = null,
		?string $name = null
	) {
		// Modules models
		$this->deviceRepository = $deviceRepository;
		$this->accountRepository = $accountRepository;
		$this->accountsManager = $accountsManager;

		$this->managerRegistry = $managerRegistry;

		$this->logger = $logger ?? new Log\NullLogger();

		$this->translator = new Translation\PrefixedTranslator($translator, $this->translationDomain);

		parent::__construct($name);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function configure(): void
	{
		parent::configure();

		$this
			->setName('fb:vernemq-plugin:create:synchronise')
			->addOption('noconfirm', null, Input\InputOption::VALUE_NONE, 'do not ask for any confirmation')
			->setDescription('Synchronize Verne MQ accounts.')
			->setHelp('This command synchronize all accounts with Verne MQ');
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute(Input\InputInterface $input, Output\OutputInterface $output)
	{
		$io = new Style\SymfonyStyle($input, $output);

		$io->title('FB Verne MQ plugin - Verne MQ accounts synchronization');

		$findDevice = new DevicesModuleQueries\FindDevicesQuery();

		$devices = $this->deviceRepository->findAllBy($findDevice, DevicesModuleEntities\Devices\NetworkDevice::class);

		foreach ($devices as $device) {
			if (!$device instanceof DevicesModuleEntities\Devices\INetworkDevice || $device->getCredentials() === null) {
				continue;
			}

			$findAccount = new Queries\FindAccountQuery();
			$findAccount->forDevice($device);

			$account = $this->accountRepository->findOneBy($findAccount);

			if ($account !== null) {
				$update = Utils\ArrayHash::from([
					'username' => $device->getCredentials()->getUsername(),
					'password' => $device->getCredentials()->getPassword(),
				]);

				try {
					// Start transaction connection to the database
					$this->getOrmConnection()
						->beginTransaction();

					$this->accountsManager->update($account, $update);

					// Commit all changes into database
					$this->getOrmConnection()
						->commit();

					$io->text(sprintf('<bg=green;options=bold> Updated </> <info>%s</info>', $account->getUsername()));

				} catch (Throwable $ex) {
					// Revert all changes when error occur
					if ($this->getOrmConnection()->isTransactionActive()) {
						$this->getOrmConnection()
							->rollBack();
					}

					$this->logger->error($ex->getMessage());

					$io->text(sprintf('<error>%s</error>', $this->translator->translate('validation.notFinished', ['error' => $ex->getMessage()])));
				}

			} else {
				$publishAcls = [];
				$subscribeAcls = [];

				$publishRule = new stdClass();
				$publishRule->pattern = '/fb/+/' . $device->getCredentials()->getUsername() . '/#';

				$publishAcls[] = $publishRule;

				$subscribeRule = new stdClass();
				$subscribeRule->pattern = '/fb/+/' . $device->getCredentials()->getUsername() . '/#';

				$subscribeAcls[] = $subscribeRule;

				$create = Utils\ArrayHash::from([
					'type'         => Types\AccountType::get(Types\AccountType::TYPE_DEVICE),
					'username'     => $device->getCredentials()->getUsername(),
					'password'     => $device->getCredentials()->getPassword(),
					'device'       => $device,
					'publishAcl'   => $publishAcls,
					'subscribeAcl' => $subscribeAcls,
				]);

				try {
					// Start transaction connection to the database
					$this->getOrmConnection()
						->beginTransaction();

					$account = $this->accountsManager->create($create);

					// Commit all changes into database
					$this->getOrmConnection()
						->commit();

					$io->text(sprintf('<bg=green;options=bold> Created </> <info>%s</info>', $account->getUsername()));

				} catch (Throwable $ex) {
					// Revert all changes when error occur
					if ($this->getOrmConnection()->isTransactionActive()) {
						$this->getOrmConnection()
							->rollBack();
					}

					$this->logger->error($ex->getMessage());

					$io->text(sprintf('<error>%s</error>', $this->translator->translate('validation.sync.notFinished', ['error' => $ex->getMessage()])));
				}
			}
		}

		$io->text([
			'',
			'<info>Synchronization complete</info>',
			'',
		]);

		return 0;
	}

	/**
	 * @return Connection
	 */
	protected function getOrmConnection(): Connection
	{
		$connection = $this->managerRegistry->getConnection();

		if ($connection instanceof Connection) {
			return $connection;
		}

		throw new Exceptions\RuntimeException('Entity manager could not be loaded');
	}

}
