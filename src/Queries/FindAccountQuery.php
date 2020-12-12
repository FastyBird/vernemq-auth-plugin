<?php declare(strict_types = 1);

/**
 * FindAccountQuery.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:VerneMqAuthPlugin!
 * @subpackage     Queries
 * @since          0.1.0
 *
 * @date           13.06.20
 */

namespace FastyBird\VerneMqAuthPlugin\Queries;

use Closure;
use Doctrine\ORM;
use FastyBird\DevicesModule\Entities as DevicesModuleEntities;
use FastyBird\VerneMqAuthPlugin\Entities;
use IPub\DoctrineOrmQuery;
use Ramsey\Uuid;

/**
 * Find accounts entities query
 *
 * @package          FastyBird:VerneMqAuthPlugin!
 * @subpackage       Queries
 *
 * @author           Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-template T of Entities\Accounts\Account
 * @phpstan-extends  DoctrineOrmQuery\QueryObject<T>
 */
class FindAccountQuery extends DoctrineOrmQuery\QueryObject
{

	/** @var Closure[] */
	private $filter = [];

	/** @var Closure[] */
	private $select = [];

	/**
	 * @param Uuid\UuidInterface $id
	 *
	 * @return void
	 */
	public function byId(Uuid\UuidInterface $id): void
	{
		$this->filter[] = function (ORM\QueryBuilder $qb) use ($id): void {
			$qb->andWhere('a.id = :id')->setParameter('id', $id->getBytes());
		};
	}

	/**
	 * @param string $username
	 *
	 * @return void
	 */
	public function byUsername(string $username): void
	{
		$this->filter[] = function (ORM\QueryBuilder $qb) use ($username): void {
			$qb->andWhere('a.username = :username')->setParameter('username', $username);
		};
	}

	/**
	 * @param DevicesModuleEntities\Devices\IDevice $device
	 *
	 * @return void
	 */
	public function forDevice(DevicesModuleEntities\Devices\IDevice $device): void
	{
		$this->select[] = function (ORM\QueryBuilder $qb): void {
			$qb->join('a.device', 'device');
		};

		$this->filter[] = function (ORM\QueryBuilder $qb) use ($device): void {
			$qb->andWhere('device.id = :device')->setParameter('device', $device->getId(), Uuid\Doctrine\UuidBinaryType::NAME);
		};
	}

	/**
	 * @param ORM\EntityRepository<Entities\Accounts\Account> $repository
	 *
	 * @return ORM\QueryBuilder
	 *
	 * @phpstan-param ORM\EntityRepository<T> $repository
	 */
	protected function doCreateQuery(ORM\EntityRepository $repository): ORM\QueryBuilder
	{
		return $this->createBasicDql($repository);
	}

	/**
	 * @param ORM\EntityRepository<Entities\Accounts\Account> $repository
	 *
	 * @return ORM\QueryBuilder
	 *
	 * @phpstan-param ORM\EntityRepository<T> $repository
	 */
	private function createBasicDql(ORM\EntityRepository $repository): ORM\QueryBuilder
	{
		$qb = $repository->createQueryBuilder('a');

		foreach ($this->select as $modifier) {
			$modifier($qb);
		}

		foreach ($this->filter as $modifier) {
			$modifier($qb);
		}

		return $qb;
	}

	/**
	 * @param ORM\EntityRepository<Entities\Accounts\Account> $repository
	 *
	 * @return ORM\QueryBuilder
	 *
	 * @phpstan-param ORM\EntityRepository<T> $repository
	 */
	protected function doCreateCountQuery(ORM\EntityRepository $repository): ORM\QueryBuilder
	{
		return $this->createBasicDql($repository)->select('COUNT(a.id)');
	}

}
