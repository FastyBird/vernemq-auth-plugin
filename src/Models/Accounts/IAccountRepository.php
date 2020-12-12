<?php declare(strict_types = 1);

/**
 * IAuthenticationRepository.php
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

use FastyBird\VerneMqAuthPlugin\Entities;
use FastyBird\VerneMqAuthPlugin\Models;
use FastyBird\VerneMqAuthPlugin\Queries;

/**
 * VerneMQ account repository interface
 *
 * @package        FastyBird:VerneMqAuthPlugin!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IAccountRepository
{

	/**
	 * @param Queries\FindAccountQuery $queryObject
	 *
	 * @return Entities\Accounts\IAccount|null
	 *
	 * @phpstan-template T of Entities\Accounts\Account
	 * @phpstan-param    Queries\FindAccountQuery<T> $queryObject
	 */
	public function findOneBy(Queries\FindAccountQuery $queryObject): ?Entities\Accounts\IAccount;

	/**
	 * @param Queries\FindAccountQuery $queryObject
	 *
	 * @return Entities\Accounts\IAccount[]
	 *
	 * @phpstan-template T of Entities\Accounts\Account
	 * @phpstan-param    Queries\FindAccountQuery<T> $queryObject
	 */
	public function findAllBy(Queries\FindAccountQuery $queryObject): array;

}
