<?php declare(strict_types = 1);

/**
 * AuthenticationRepository.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:VerneMqAuthPlugin!
 * @subpackage     Models
 * @since          0.1.0
 *
 * @date           13.06.20
 */

namespace FastyBird\VerneMqAuthPlugin\Models\Accounts;

use Doctrine\Common;
use Doctrine\Persistence;
use FastyBird\VerneMqAuthPlugin\Entities;
use FastyBird\VerneMqAuthPlugin\Queries;
use Nette;
use Throwable;

/**
 * VerneMQ account repository
 *
 * @package        FastyBird:VerneMqAuthPlugin!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class AccountRepository implements IAccountRepository
{

	use Nette\SmartObject;

	/** @var Common\Persistence\ManagerRegistry */
	private Common\Persistence\ManagerRegistry $managerRegistry;

	/** @var Persistence\ObjectRepository<Entities\Accounts\Account>|null */
	private ?Persistence\ObjectRepository $repository = null;

	public function __construct(
		Common\Persistence\ManagerRegistry $managerRegistry
	) {
		$this->managerRegistry = $managerRegistry;
	}

	/**
	 * {@inheritDoc}
	 */
	public function findOneBy(Queries\FindAccountQuery $queryObject): ?Entities\Accounts\IAccount
	{
		/** @var Entities\Accounts\IAccount|null $role */
		$role = $queryObject->fetchOne($this->getRepository());

		return $role;
	}

	/**
	 * @return Persistence\ObjectRepository<Entities\Accounts\Account>
	 */
	private function getRepository(): Persistence\ObjectRepository
	{
		if ($this->repository === null) {
			$this->repository = $this->managerRegistry->getRepository(Entities\Accounts\Account::class);
		}

		return $this->repository;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws Throwable
	 */
	public function findAllBy(Queries\FindAccountQuery $queryObject): array
	{
		$result = $queryObject->fetch($this->getRepository());

		return is_array($result) ? $result : $result->toArray();
	}

}
