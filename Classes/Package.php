<?php
namespace Flowpack\Monolog;

use TYPO3\Flow\Configuration\ConfigurationManager;
use TYPO3\Flow\Core\Bootstrap;
use TYPO3\Flow\Package\Package as BasePackage;
use TYPO3\Flow\Utility\Files;

/**
 *
 */
class Package extends BasePackage {
	/**
	 * Invokes custom PHP code directly after the package manager has been initialized.
	 *
	 * @param Bootstrap $bootstrap The current bootstrap
	 * @return void
	 */
	public function boot(Bootstrap $bootstrap) {
		if (!file_exists(FLOW_PATH_DATA . 'Logs')) {
			Files::createDirectoryRecursively(FLOW_PATH_DATA . 'Logs');
		}


		$monologFactory = LoggerFactory::getInstance();
		$bootstrap->setEarlyInstance(LoggerFactory::class, $monologFactory);

		$dispatcher = $bootstrap->getSignalSlotDispatcher();

		$dispatcher->connect('TYPO3\Flow\Core\Booting\Sequence', 'afterInvokeStep', function ($step) use ($bootstrap, $dispatcher) {
			if ($step->getIdentifier() === 'typo3.flow:configuration') {
				/** @var ConfigurationManager $configurationManager */
				$configurationManager = $bootstrap->getEarlyInstance(ConfigurationManager::class);
				$monologFactory = LoggerFactory::getInstance();

				$loggerConfigurations = $configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'Flowpack.Monolog');
				$monologFactory->injectConfiguration($loggerConfigurations);
			}
		});
	}

}