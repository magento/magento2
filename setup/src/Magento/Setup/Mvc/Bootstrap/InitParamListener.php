<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Mvc\Bootstrap;

use Magento\Framework\App\Bootstrap as AppBootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\Framework\Shell\ComplexParameter;
use Magento\Framework\Setup\Mvc\MvcApplication;
use Magento\Framework\Setup\Mvc\MvcEvent;
use Laminas\EventManager\EventManagerInterface;

/**
 * A listener that injects relevant Magento initialization parameters and initializes filesystem
 * @deprecated Web Setup support has been removed, this class is no longer in use.
 * @see we don't use it anymore
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @codingStandardsIgnoreStart
 */
class InitParamListener
{
    /**
     * A CLI parameter for injecting bootstrap variables
     */
    const BOOTSTRAP_PARAM = 'magento-init-params';

    /**
     * Attach listener to events (compatibility method for tests)
     *
     * @param EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events): void
    {
        $sharedManager = $events->getSharedManager();
        if ($sharedManager) {
            $sharedManager->attach(
                MvcApplication::class,
                MvcEvent::EVENT_BOOTSTRAP,
                [$this, 'onBootstrap']
            );
            // Get existing listeners (as expected by the test)
            $sharedManager->getListeners([MvcApplication::class], MvcEvent::EVENT_BOOTSTRAP);
        }
    }

    /**
     * Detach listener from events (compatibility method for tests)
     *
     * @param EventManagerInterface $events
     * @return void
     */
    public function detach(EventManagerInterface $events): void
    {
        $events->detach([$this, 'onBootstrap']);
    }

    /**
     * An event subscriber that initializes DirectoryList and Filesystem objects in ZF application bootstrap
     *
     * @param MvcEvent $e
     * @return void
     */
    public function onBootstrap(MvcEvent $e): void
    {
        /** @var MvcApplication $application */
        $application = $e->getApplication();
        $initParams = $application->getServiceManager()->get(self::BOOTSTRAP_PARAM);
        $directoryList = $this->createDirectoryList($initParams);
        $serviceManager = $application->getServiceManager();
        $serviceManager->setService(\Magento\Framework\App\Filesystem\DirectoryList::class, $directoryList);
        $serviceManager->setService(\Magento\Framework\Filesystem::class, $this->createFilesystem($directoryList));
    }

    /**
     * Create service (compatibility method for tests)
     *
     * @param mixed $serviceLocator
     * @return array
     */
    public function createService($serviceLocator): array
    {
        $application = $serviceLocator->get('Application');
        $config = $application->getConfig();
        return $this->extractInitParametersFromConfig($config);
    }

    /**
     * Factory method for creating init parameters (compatible with Laminas ServiceManager)
     *
     * @param mixed $serviceManager Laminas ServiceManager
     * @param string $requestedName
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke($serviceManager, $requestedName): array
    {
        // For Laminas ServiceManager, extract parameters from merged config (which includes global.php)
        $mergedConfig = $serviceManager->has('config') ? $serviceManager->get('config') : [];
        $appConfig = $serviceManager->has('ApplicationConfig') ? $serviceManager->get('ApplicationConfig') : [];

        // Merge both configs to ensure we get bootstrap params from global.php
        $fullConfig = array_merge_recursive($appConfig, $mergedConfig);

        return $this->extractInitParametersFromConfig($fullConfig);
    }

    /**
     * Collects init params configuration from multiple sources
     *
     * Each next step overwrites previous, whenever data is available, in the following order:
     * 1: ZF application config
     * 2: environment
     * 3: CLI parameters (if the application is running in CLI mode)
     *
     * @param array $config
     * @return array
     */
    private function extractInitParametersFromConfig(array $config): array
    {
        $result = [];
        if (isset($config[self::BOOTSTRAP_PARAM])) {
            $result = $config[self::BOOTSTRAP_PARAM];
        }
        foreach ([State::PARAM_MODE, AppBootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS] as $initKey) {
            if (isset($_SERVER[$initKey])) {
                $result[$initKey] = $_SERVER[$initKey];
            }
        }

        if (!isset($result['argv']) || !is_array($result['argv'])) {
            return $result;
        }

        return array_replace_recursive($result, $this->extractFromCli($result['argv']));
    }

    /**
     * Extracts the directory paths from a CLI request
     *
     * Uses format of a URL query
     *
     * @param array $argv
     * @return array
     */
    private function extractFromCli(array $argv): array
    {
        $bootstrapParam = new ComplexParameter(self::BOOTSTRAP_PARAM);
        foreach ($argv as $paramStr) {
            $result = $bootstrapParam->getFromString($paramStr);
            if (!empty($result)) {
                return $result;
            }
        }
        return [];
    }

    /**
     * Initializes DirectoryList service
     *
     * @param array $initParams
     * @return DirectoryList
     * @throws \LogicException
     */
    public function createDirectoryList($initParams): DirectoryList
    {
        if (!isset($initParams[AppBootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS][DirectoryList::ROOT])) {
            throw new \LogicException('Magento root directory is not specified.');
        }
        $config = $initParams[AppBootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS];
        $rootDir = $config[DirectoryList::ROOT][DirectoryList::PATH];
        return new DirectoryList($rootDir, $config);
    }

    /**
     * Initializes Filesystem service
     *
     * @param DirectoryList $directoryList
     * @return Filesystem
     */
    public function createFilesystem(DirectoryList $directoryList): Filesystem
    {
        $driverPool = new Filesystem\DriverPool();
        return new Filesystem(
            $directoryList,
            new Filesystem\Directory\ReadFactory($driverPool),
            new Filesystem\Directory\WriteFactory($driverPool)
        );
    }
}
