<?php declare(strict_types = 1);

namespace Tests\Cases;

use FastyBird\VerneMqAuthPlugin;
use FastyBird\VerneMqAuthPlugin\Commands;
use FastyBird\VerneMqAuthPlugin\Models;
use FastyBird\VerneMqAuthPlugin\Subscribers;
use Nette;
use Ninjify\Nunjuck\TestCase\BaseMockeryTestCase;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class ExtensionTest extends BaseMockeryTestCase
{

	public function testServicesRegistration(): void
	{
		$container = $this->createContainer();

		Assert::notNull($container->getByType(Commands\Accounts\CreateCommand::class));
		Assert::notNull($container->getByType(Commands\SynchroniseCommand::class));

		Assert::notNull($container->getByType(Models\Accounts\AccountsManager::class));
		Assert::notNull($container->getByType(Models\Accounts\AccountRepository::class));

		Assert::notNull($container->getByType(Subscribers\EntitySubscriber::class));
	}

	/**
	 * @param string|null $additionalConfig
	 *
	 * @return Nette\DI\Container
	 */
	protected function createContainer(?string $additionalConfig = null): Nette\DI\Container
	{
		$rootDir = __DIR__ . '/../../../';

		$config = new Nette\Configurator();
		$config->setTempDirectory(TEMP_DIR);

		$config->addParameters(['container' => ['class' => 'SystemContainer_' . md5((string) time())]]);
		$config->addParameters(['appDir' => $rootDir, 'wwwDir' => $rootDir]);

		$config->addConfig(__DIR__ . '/../../../common.neon');

		if ($additionalConfig && file_exists($additionalConfig)) {
			$config->addConfig($additionalConfig);
		}

		VerneMqAuthPlugin\DI\VerneMqAuthPluginExtension::register($config);

		return $config->createContainer();
	}

}

$test_case = new ExtensionTest();
$test_case->run();
