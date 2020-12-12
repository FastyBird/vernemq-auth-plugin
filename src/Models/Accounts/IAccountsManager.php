<?php declare(strict_types = 1);

/**
 * IAccountsManager.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:VerneMqAuthPlugin!
 * @subpackage     Models
 * @since          0.1.0
 *
 * @date           26.06.20
 */

namespace FastyBird\VerneMqAuthPlugin\Models\Accounts;

use FastyBird\VerneMqAuthPlugin\Entities;
use FastyBird\VerneMqAuthPlugin\Models;
use Nette\Utils;

/**
 * Verne accounts entities manager interface
 *
 * @package        FastyBird:VerneMqAuthPlugin!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IAccountsManager
{

	/**
	 * @param Utils\ArrayHash $values
	 *
	 * @return Entities\Accounts\IAccount
	 */
	public function create(
		Utils\ArrayHash $values
	): Entities\Accounts\IAccount;

	/**
	 * @param Entities\Accounts\IAccount $entity
	 * @param Utils\ArrayHash $values
	 *
	 * @return Entities\Accounts\IAccount
	 */
	public function update(
		Entities\Accounts\IAccount $entity,
		Utils\ArrayHash $values
	): Entities\Accounts\IAccount;

	/**
	 * @param Entities\Accounts\IAccount $entity
	 *
	 * @return bool
	 */
	public function delete(
		Entities\Accounts\IAccount $entity
	): bool;

}
