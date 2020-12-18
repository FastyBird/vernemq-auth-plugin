<?php declare(strict_types = 1);

/**
 * EntitySubscriber.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:VerneMqAuthPlugin!
 * @subpackage     Subscribers
 * @since          0.1.0
 *
 * @date           13.06.20
 */

namespace FastyBird\VerneMqAuthPlugin\Subscribers;

use Doctrine\Common;
use Doctrine\ORM;
use FastyBird\DevicesModule\Entities as DevicesModuleEntities;
use FastyBird\VerneMqAuthPlugin\Entities;
use FastyBird\VerneMqAuthPlugin\Models;
use FastyBird\VerneMqAuthPlugin\Queries;
use FastyBird\VerneMqAuthPlugin\Types;
use Nette;
use Throwable;

/**
 * Doctrine entities events
 *
 * @package        FastyBird:VerneMqAuthPlugin!
 * @subpackage     Subscribers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class EntitySubscriber implements Common\EventSubscriber
{

	use Nette\SmartObject;

	/** @var Models\Accounts\IAccountRepository */
	private Models\Accounts\IAccountRepository $accountRepository;

	public function __construct(
		Models\Accounts\IAccountRepository $accountRepository
	) {
		$this->accountRepository = $accountRepository;
	}

	/**
	 * Register events
	 *
	 * @return string[]
	 */
	public function getSubscribedEvents(): array
	{
		return [
			ORM\Events::preFlush,
			ORM\Events::preUpdate,
			ORM\Events::preRemove,
		];
	}

	/**
	 * @param ORM\Event\PreUpdateEventArgs $eventArgs
	 *
	 * @return void
	 *
	 * @throws Throwable
	 */
	public function preUpdate(ORM\Event\PreUpdateEventArgs $eventArgs): void
	{
		$em = $eventArgs->getEntityManager();
		$uow = $em->getUnitOfWork();

		// Check all scheduled updates
		foreach ($uow->getScheduledEntityUpdates() as $object) {
			if ($object instanceof DevicesModuleEntities\Devices\Credentials\Credentials) {
				$this->processCredentialsEntity($object, $em, $uow);
			}
		}
	}

	/**
	 * @param DevicesModuleEntities\Devices\Credentials\Credentials $credentials
	 * @param ORM\EntityManager $em
	 * @param ORM\UnitOfWork $uow
	 *
	 * @return void
	 *
	 * @throws Throwable
	 */
	private function processCredentialsEntity(
		DevicesModuleEntities\Devices\Credentials\Credentials $credentials,
		ORM\EntityManager $em,
		ORM\UnitOfWork $uow
	): void {
		$account = $this->findAccount($credentials);

		if ($account === null) {
			$this->createAccount($credentials, $uow);

		} else {
			$this->updateAccount($account, $credentials, $em, $uow);
		}
	}

	/**
	 * @param DevicesModuleEntities\Devices\Credentials\Credentials $credentials
	 *
	 * @return Entities\Accounts\IAccount|null
	 */
	private function findAccount(
		DevicesModuleEntities\Devices\Credentials\Credentials $credentials
	): ?Entities\Accounts\IAccount {
		$device = $credentials->getDevice();

		$findAccount = new Queries\FindAccountQuery();
		$findAccount->forDevice($device);

		return $this->accountRepository->findOneBy($findAccount);
	}

	/**
	 * @param DevicesModuleEntities\Devices\Credentials\Credentials $credentials
	 * @param ORM\UnitOfWork $uow
	 *
	 * @return void
	 *
	 * @throws Throwable
	 */
	private function createAccount(
		DevicesModuleEntities\Devices\Credentials\Credentials $credentials,
		ORM\UnitOfWork $uow
	): void {
		$account = new Entities\Accounts\Account(
			$credentials->getUsername(),
			$credentials->getPassword(),
			Types\AccountType::get(Types\AccountType::TYPE_DEVICE)
		);

		$account->setClientId($credentials->getDevice()->getIdentifier());

		$account->addPublishAcl('/fb/+/' . $credentials->getUsername() . '/#');
		$account->addSubscribeAcl('/fb/+/' . $credentials->getUsername() . '/#');

		$account->setDevice($credentials->getDevice());

		$uow->scheduleForInsert($account);
	}

	/**
	 * @param Entities\Accounts\IAccount $account
	 * @param DevicesModuleEntities\Devices\Credentials\Credentials $credentials
	 * @param ORM\EntityManager $em
	 * @param ORM\UnitOfWork $uow
	 *
	 * @return void
	 */
	private function updateAccount(
		Entities\Accounts\IAccount $account,
		DevicesModuleEntities\Devices\Credentials\Credentials $credentials,
		ORM\EntityManager $em,
		ORM\UnitOfWork $uow
	): void {
		$classMetadata = $em->getClassMetadata(get_class($account));

		$passwordProperty = $classMetadata->getReflectionProperty('password');
		$usernameProperty = $classMetadata->getReflectionProperty('username');

		$account->setPassword($credentials->getPassword());
		$account->setUsername($credentials->getUsername());

		$uow->propertyChanged(
			$account,
			'password',
			$passwordProperty->getValue($account),
			$credentials->getPassword()
		);

		$uow->propertyChanged(
			$account,
			'username',
			$usernameProperty->getValue($account),
			$credentials->getUsername()
		);

		$uow->scheduleExtraUpdate($account, [
			'password' => [
				$passwordProperty->getValue($account),
				$credentials->getPassword(),
			],
			'username' => [
				$usernameProperty->getValue($account),
				$credentials->getUsername(),
			],
		]);
	}

	/**
	 * @param ORM\Event\PreFlushEventArgs $eventArgs
	 *
	 * @return void
	 *
	 * @throws Throwable
	 */
	public function preFlush(ORM\Event\PreFlushEventArgs $eventArgs): void
	{
		$em = $eventArgs->getEntityManager();
		$uow = $em->getUnitOfWork();

		// Check all scheduled updates
		foreach ($uow->getScheduledEntityInsertions() as $object) {
			if ($object instanceof DevicesModuleEntities\Devices\Credentials\Credentials) {
				$this->processCredentialsEntity($object, $em, $uow);
			}
		}
	}

	/**
	 * @param ORM\Event\LifecycleEventArgs $eventArgs
	 *
	 * @return void
	 *
	 * @throws Throwable
	 */
	public function preRemove(ORM\Event\LifecycleEventArgs $eventArgs): void
	{
		$em = $eventArgs->getEntityManager();
		$uow = $em->getUnitOfWork();

		foreach (array_merge($uow->getScheduledEntityDeletions(), $uow->getScheduledCollectionDeletions()) as $object) {
			if ($object instanceof DevicesModuleEntities\Devices\Credentials\Credentials) {
				$findAccount = new Queries\FindAccountQuery();
				$findAccount->forDevice($object->getDevice());

				$accounts = $this->accountRepository->findAllBy($findAccount);

				foreach ($accounts as $account) {
					$uow->scheduleForDelete($account);
				}
			}
		}
	}

}
