<?php declare(strict_types = 1);

/**
 * CreateCommand.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:VerneMqAuthPlugin!
 * @subpackage     Commands
 * @since          0.1.0
 *
 * @date           31.03.20
 */

namespace FastyBird\VerneMqAuthPlugin\Commands\Accounts;

use Contributte\Translation;
use Doctrine\Common;
use Doctrine\DBAL\Connection;
use FastyBird\VerneMqAuthPlugin\Entities;
use FastyBird\VerneMqAuthPlugin\Exceptions;
use FastyBird\VerneMqAuthPlugin\Models;
use Nette\Utils;
use Psr\Log;
use Symfony\Component\Console;
use Symfony\Component\Console\Input;
use Symfony\Component\Console\Output;
use Symfony\Component\Console\Style;
use Throwable;

/**
 * Account creation command
 *
 * @package        FastyBird:VerneMqAuthPlugin!
 * @subpackage     Commands
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class CreateCommand extends Console\Command\Command
{

	/** @var Models\Accounts\IAccountsManager */
	private Models\Accounts\IAccountsManager $accountsManager;

	/** @var Common\Persistence\ManagerRegistry */
	private Common\Persistence\ManagerRegistry $managerRegistry;

	/** @var Log\LoggerInterface */
	private Log\LoggerInterface $logger;

	/** @var Translation\PrefixedTranslator */
	private Translation\PrefixedTranslator $translator;

	/** @var string */
	private string $translationDomain = 'commands.create';

	public function __construct(
		Models\Accounts\IAccountsManager $accountsManager,
		Common\Persistence\ManagerRegistry $managerRegistry,
		Translation\Translator $translator,
		?Log\LoggerInterface $logger = null,
		?string $name = null
	) {
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
		$this
			->setName('fb:vernemq-plugin:create:account')
			->addArgument('username', Input\InputArgument::OPTIONAL, $this->translator->translate('inputs.username.title'))
			->addArgument('password', Input\InputArgument::OPTIONAL, $this->translator->translate('inputs.password.title'))
			->addArgument('clientid', Input\InputArgument::OPTIONAL, $this->translator->translate('inputs.clientId.title'))
			->addOption('noconfirm', null, Input\InputOption::VALUE_NONE, 'do not ask for any confirmation')
			->setDescription('Create Verne MQ account.');
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute(Input\InputInterface $input, Output\OutputInterface $output)
	{
		$io = new Style\SymfonyStyle($input, $output);

		$io->title('FB Verne MQ plugin - create Verne MQ account');

		if (
			$input->hasArgument('username')
			&& is_string($input->getArgument('username'))
			&& $input->getArgument('username') !== ''
		) {
			$username = $input->getArgument('username');

		} else {
			$username = $io->ask($this->translator->translate('inputs.username.title'));
		}

		if (
			$input->hasArgument('password')
			&& is_string($input->getArgument('password'))
			&& $input->getArgument('password') !== ''
		) {
			$password = $input->getArgument('password');

		} else {
			$password = $io->ask($this->translator->translate('inputs.password.title'));
		}

		if (
			$input->hasArgument('clientid')
			&& is_string($input->getArgument('clientid'))
			&& $input->getArgument('clientid') !== ''
		) {
			$clientId = $input->getArgument('clientid');

		} else {
			$clientId = $io->ask($this->translator->translate('inputs.clientId.title'));
		}

		$roleName = $io->choice(
			$this->translator->translate('inputs.role.title'),
			[
				'U' => $this->translator->translate('inputs.role.values.user'),
				'M' => $this->translator->translate('inputs.role.values.manager'),
				'D' => $this->translator->translate('inputs.role.values.device'),
			],
			'U'
		);

		$publishAcls = [];
		$subscribeAcls = [];

		switch ($roleName) {
			case 'U':
				$subscribeAcls = [
					'/fb/#',
				];
				break;

			case 'M':
				$publishAcls = [
					'/fb/#',
				];

				$subscribeAcls = [
					'/fb/#',
					'$SYS/broker/log/#',
				];
				break;

			case 'D':
				$publishAcls = [
					'/fb/+/' . $username . '/#',
				];

				$subscribeAcls = [
					'/fb/+/' . $username . '/#',
				];
				break;
		}

		try {
			// Start transaction connection to the database
			$this->getOrmConnection()
				->beginTransaction();

			$create = Utils\ArrayHash::from([
				'entity'       => Entities\Accounts\Account::class,
				'username'     => $username,
				'password'     => $password,
				'clientId'     => $clientId,
				'publishAcl'   => $publishAcls,
				'subscribeAcl' => $subscribeAcls,
			]);

			$account = $this->accountsManager->create($create);

			// Commit all changes into database
			$this->getOrmConnection()
				->commit();

		} catch (Throwable $ex) {
			// Revert all changes when error occur
			if ($this->getOrmConnection()
				->isTransactionActive()) {
				$this->getOrmConnection()
					->rollBack();
			}

			$this->logger->error($ex->getMessage());

			$io->error($this->translator->translate('validation.account.wasNotCreated', ['error' => $ex->getMessage()]));

			return $ex->getCode();
		}

		$io->success($this->translator->translate('success', ['name' => $account->getUsername()]));

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
