<?php declare(strict_types = 1);

/**
 * AccountType.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:VerneMqAuthPlugin!
 * @subpackage     Types
 * @since          0.1.0
 *
 * @date           12.12.20
 */

namespace FastyBird\VerneMqAuthPlugin\Types;

use Consistence;

/**
 * Account type types
 *
 * @package        FastyBird:VerneMqAuthPlugin!
 * @subpackage     Types
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class AccountType extends Consistence\Enum\Enum
{

	/**
	 * Define device states
	 */
	public const TYPE_DEVICE = 'device';
	public const TYPE_USER = 'user';

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return (string) self::getValue();
	}

}
