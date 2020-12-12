<?php declare(strict_types = 1);

/**
 * VerneMqAuthPluginExtension.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:VerneMqAuthPlugin!
 * @subpackage     DI
 * @since          0.1.0
 *
 * @date           12.12.20
 */

namespace FastyBird\VerneMqAuthPlugin\DI;

use Contributte\Translation;
use Doctrine\Persistence;
use FastyBird\VerneMqAuthPlugin\Commands;
use FastyBird\VerneMqAuthPlugin\Entities;
use FastyBird\VerneMqAuthPlugin\Models;
use FastyBird\VerneMqAuthPlugin\Subscribers;
use IPub\DoctrineCrud;
use Nette;
use Nette\DI;
use Nette\PhpGenerator;

/**
 * Verne MQ authentication extension container
 *
 * @package        FastyBird:VerneMqAuthPlugin!
 * @subpackage     DI
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class VerneMqAuthPluginExtension extends DI\CompilerExtension implements Translation\DI\TranslationProviderInterface
{

	/**
	 * @param Nette\Configurator $config
	 * @param string $extensionName
	 *
	 * @return void
	 */
	public static function register(
		Nette\Configurator $config,
		string $extensionName = 'fbVerneMqAuthPlugin'
	): void {
		$config->onCompile[] = function (
			Nette\Configurator $config,
			DI\Compiler $compiler
		) use ($extensionName): void {
			$compiler->addExtension($extensionName, new VerneMqAuthPluginExtension());
		};
	}

	/**
	 * {@inheritDoc}
	 */
	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();

		// Console commands
		$builder->addDefinition(null)
			->setType(Commands\Accounts\CreateCommand::class);

		$builder->addDefinition(null)
			->setType(Commands\SynchroniseCommand::class);

		// Database repositories
		$builder->addDefinition(null)
			->setType(Models\Accounts\AccountRepository::class);

		// Database managers
		$builder->addDefinition($this->prefix('doctrine.accountsManager'))
			->setType(Models\Accounts\AccountsManager::class)
			->setArgument('entityCrud', '__placeholder__');

		// Event subscribers
		$builder->addDefinition(null)
			->setType(Subscribers\EntitySubscriber::class);
	}

	/**
	 * {@inheritDoc}
	 */
	public function beforeCompile(): void
	{
		parent::beforeCompile();

		$builder = $this->getContainerBuilder();

		/**
		 * Doctrine entities
		 */

		$ormAnnotationDriverService = $builder->getDefinition('nettrineOrmAnnotations.annotationDriver');

		if ($ormAnnotationDriverService instanceof DI\Definitions\ServiceDefinition) {
			$ormAnnotationDriverService->addSetup('addPaths', [[__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Entities']]);
		}

		$ormAnnotationDriverChainService = $builder->getDefinitionByType(Persistence\Mapping\Driver\MappingDriverChain::class);

		if ($ormAnnotationDriverChainService instanceof DI\Definitions\ServiceDefinition) {
			$ormAnnotationDriverChainService->addSetup('addDriver', [
				$ormAnnotationDriverService,
				'FastyBird\VerneMqAuthPlugin\Entities',
			]);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function afterCompile(
		PhpGenerator\ClassType $class
	): void {
		$builder = $this->getContainerBuilder();

		$entityFactoryServiceName = $builder->getByType(DoctrineCrud\Crud\IEntityCrudFactory::class, true);

		$accountsManagerService = $class->getMethod('createService' . ucfirst($this->name) . '__doctrine__accountsManager');
		$accountsManagerService->setBody('return new ' . Models\Accounts\AccountsManager::class . '($this->getService(\'' . $entityFactoryServiceName . '\')->create(\'' . Entities\Accounts\Account::class . '\'));');
	}

	/**
	 * @return string[]
	 */
	public function getTranslationResources(): array
	{
		return [
			__DIR__ . '/../Translations',
		];
	}

}
