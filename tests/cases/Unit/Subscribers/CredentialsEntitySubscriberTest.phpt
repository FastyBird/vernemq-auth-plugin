<?php declare(strict_types = 1);

namespace Tests\Cases;

use FastyBird\DevicesModule\Entities as DevicesModuleEntities;
use FastyBird\DevicesModule\Models as DevicesModuleModels;
use FastyBird\DevicesModule\Queries as DevicesModuleQueries;
use FastyBird\VerneMqAuthPlugin\Models;
use FastyBird\VerneMqAuthPlugin\Queries;
use Nette\Utils;
use Ramsey\Uuid;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';
require_once __DIR__ . '/../DbTestCase.php';

/**
 * @testCase
 */
final class CredentialsEntitySubscriberTest extends DbTestCase
{

	private const DEVICE_TEST_ID = 'bf4cd870-2aac-45f0-a85e-e1cefd2d6d9a';
	private const DEVICE_WITH_CREDENTIALS_TEST_ID = '69786d15-fd0c-4d9f-9378-33287c2009fa';

	public function testCreateEntity(): void
	{
		/** @var DevicesModuleModels\Devices\DeviceRepository $deviceRepository */
		$deviceRepository = $this->getContainer()
			->getByType(DevicesModuleModels\Devices\DeviceRepository::class);

		$findDevice = new DevicesModuleQueries\FindDevicesQuery();
		$findDevice->byId(Uuid\Uuid::fromString(self::DEVICE_TEST_ID));

		/** @var DevicesModuleEntities\Devices\INetworkDevice $device */
		$device = $deviceRepository->findOneBy($findDevice, DevicesModuleEntities\Devices\NetworkDevice::class);

		Assert::notNull($device);
		Assert::type(DevicesModuleEntities\Devices\INetworkDevice::class, $device);
		Assert::null($device->getCredentials());

		$createCredentials = Utils\ArrayHash::from([
			'entity'   => DevicesModuleEntities\Devices\Credentials\Credentials::class,
			'device'   => $device,
			'password' => 'randomPassword',
			'username' => 'newUsername',
		]);

		/** @var DevicesModuleModels\Devices\Credentials\CredentialsManager $credentialsManager */
		$credentialsManager = $this->getContainer()
			->getByType(DevicesModuleModels\Devices\Credentials\CredentialsManager::class);

		$credentials = $credentialsManager->create($createCredentials);

		Assert::notNull($credentials);
		Assert::type(DevicesModuleEntities\Devices\Credentials\ICredentials::class, $credentials);

		/** @var Models\Accounts\IAccountRepository $accountRepository */
		$accountRepository = $this->getContainer()
			->getByType(Models\Accounts\AccountRepository::class);

		$findAccount = new Queries\FindAccountQuery();
		$findAccount->forDevice($device);

		$account = $accountRepository->findOneBy($findAccount);

		Assert::notNull($account);
		Assert::same(hash('sha256', $credentials->getPassword(), false), $account->getPassword());
		Assert::same('newUsername', $account->getUsername());
	}

	public function testUpdateEntityPasswordUsername(): void
	{
		/** @var DevicesModuleModels\Devices\DeviceRepository $deviceRepository */
		$deviceRepository = $this->getContainer()
			->getByType(DevicesModuleModels\Devices\DeviceRepository::class);

		$findDevice = new DevicesModuleQueries\FindDevicesQuery();
		$findDevice->byId(Uuid\Uuid::fromString(self::DEVICE_WITH_CREDENTIALS_TEST_ID));

		/** @var DevicesModuleEntities\Devices\INetworkDevice $device */
		$device = $deviceRepository->findOneBy($findDevice, DevicesModuleEntities\Devices\NetworkDevice::class);

		Assert::notNull($device);
		Assert::type(DevicesModuleEntities\Devices\INetworkDevice::class, $device);
		Assert::notNull($device->getCredentials());

		$updateCredentials = Utils\ArrayHash::from([
			'password' => 'randomPassword',
			'username' => 'newUsername',
		]);

		/** @var DevicesModuleModels\Devices\Credentials\CredentialsManager $credentialsManager */
		$credentialsManager = $this->getContainer()
			->getByType(DevicesModuleModels\Devices\Credentials\CredentialsManager::class);

		/** @var DevicesModuleEntities\Devices\Credentials\ICredentials $credentials */
		$credentials = $credentialsManager->update($device->getCredentials(), $updateCredentials);

		/** @var Models\Accounts\IAccountRepository $accountRepository */
		$accountRepository = $this->getContainer()
			->getByType(Models\Accounts\AccountRepository::class);

		$findAccount = new Queries\FindAccountQuery();
		$findAccount->forDevice($device);

		$account = $accountRepository->findOneBy($findAccount);

		Assert::notNull($account);
		Assert::same(hash('sha256', $credentials->getPassword(), false), $account->getPassword());
		Assert::same('newUsername', $account->getUsername());
	}

	public function testUpdateEntityPassword(): void
	{
		/** @var DevicesModuleModels\Devices\DeviceRepository $deviceRepository */
		$deviceRepository = $this->getContainer()
			->getByType(DevicesModuleModels\Devices\DeviceRepository::class);

		$findDevice = new DevicesModuleQueries\FindDevicesQuery();
		$findDevice->byId(Uuid\Uuid::fromString(self::DEVICE_WITH_CREDENTIALS_TEST_ID));

		/** @var DevicesModuleEntities\Devices\INetworkDevice $device */
		$device = $deviceRepository->findOneBy($findDevice, DevicesModuleEntities\Devices\NetworkDevice::class);

		Assert::notNull($device);
		Assert::type(DevicesModuleEntities\Devices\INetworkDevice::class, $device);
		Assert::notNull($device->getCredentials());

		$updateCredentials = Utils\ArrayHash::from([
			'password' => 'randomPassword',
		]);

		/** @var DevicesModuleModels\Devices\Credentials\CredentialsManager $credentialsManager */
		$credentialsManager = $this->getContainer()
			->getByType(DevicesModuleModels\Devices\Credentials\CredentialsManager::class);

		/** @var DevicesModuleEntities\Devices\Credentials\ICredentials $credentials */
		$credentials = $credentialsManager->update($device->getCredentials(), $updateCredentials);

		/** @var Models\Accounts\IAccountRepository $accountRepository */
		$accountRepository = $this->getContainer()
			->getByType(Models\Accounts\AccountRepository::class);

		$findAccount = new Queries\FindAccountQuery();
		$findAccount->forDevice($device);

		$account = $accountRepository->findOneBy($findAccount);

		Assert::notNull($account);
		Assert::same(hash('sha256', $credentials->getPassword(), false), $account->getPassword());
		Assert::same('deviceUsername', $account->getUsername());
	}

	public function testDeleteEntity(): void
	{
		/** @var DevicesModuleModels\Devices\DeviceRepository $deviceRepository */
		$deviceRepository = $this->getContainer()
			->getByType(DevicesModuleModels\Devices\DeviceRepository::class);

		$findDevice = new DevicesModuleQueries\FindDevicesQuery();
		$findDevice->byId(Uuid\Uuid::fromString(self::DEVICE_WITH_CREDENTIALS_TEST_ID));

		/** @var DevicesModuleEntities\Devices\INetworkDevice $device */
		$device = $deviceRepository->findOneBy($findDevice, DevicesModuleEntities\Devices\NetworkDevice::class);

		Assert::notNull($device);
		Assert::type(DevicesModuleEntities\Devices\INetworkDevice::class, $device);

		/** @var DevicesModuleModels\Devices\DevicesManager $devicesManager */
		$devicesManager = $this->getContainer()
			->getByType(DevicesModuleModels\Devices\DevicesManager::class);

		$devicesManager->delete($device);

		/** @var Models\Accounts\IAccountRepository $accountRepository */
		$accountRepository = $this->getContainer()
			->getByType(Models\Accounts\AccountRepository::class);

		$findAccount = new Queries\FindAccountQuery();

		$account = $accountRepository->findOneBy($findAccount);

		Assert::null($account);
	}

}

$test_case = new CredentialsEntitySubscriberTest();
$test_case->run();
