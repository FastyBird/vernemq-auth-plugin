<?php declare(strict_types = 1);

/**
 * IAccount.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:VerneMqAuthPlugin!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           13.06.20
 */

namespace FastyBird\VerneMqAuthPlugin\Entities\Accounts;

use FastyBird\Database\Entities as DatabaseEntities;
use FastyBird\VerneMqAuthPlugin\Types;
use IPub\DoctrineTimestampable;

/**
 * Account entity interface
 *
 * @package        FastyBird:VerneMqAuthPlugin!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IAccount extends DatabaseEntities\IEntity,
	DoctrineTimestampable\Entities\IEntityCreated,
	DoctrineTimestampable\Entities\IEntityUpdated
{

	/**
	 * @return Types\AccountType
	 */
	public function getType(): Types\AccountType;

	/**
	 * @param string $mountpoint
	 *
	 * @return void
	 */
	public function setMountpoint(string $mountpoint): void;

	/**
	 * @return string
	 */
	public function getMountpoint(): string;

	/**
	 * @param string $clientId
	 *
	 * @return void
	 */
	public function setClientId(string $clientId): void;

	/**
	 * @return string
	 */
	public function getClientId(): string;

	/**
	 * @param string $username
	 *
	 * @return void
	 */
	public function setUsername(string $username): void;

	/**
	 * @return string
	 */
	public function getUsername(): string;

	/**
	 * @param string $password
	 *
	 * @return void
	 */
	public function setPassword(string $password): void;

	/**
	 * @return string
	 */
	public function getPassword(): string;

	/**
	 * @param string[] $publishAcl
	 *
	 * @return void
	 */
	public function setPublishAcl(array $publishAcl): void;

	/**
	 * @param string $pattern
	 *
	 * @return void
	 */
	public function addPublishAcl(string $pattern): void;

	/**
	 * @return mixed[]
	 */
	public function getPublishAcl(): array;

	/**
	 * @param string[] $subscribeAcl
	 *
	 * @return void
	 */
	public function setSubscribeAcl(array $subscribeAcl): void;

	/**
	 * @param string $pattern
	 *
	 * @return void
	 */
	public function addSubscribeAcl(string $pattern): void;

	/**
	 * @return mixed[]
	 */
	public function getSubscribeAcl(): array;

}
