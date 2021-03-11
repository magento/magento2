<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Mvc\Bootstrap;

use Magento\Framework\App\Bootstrap as AppBootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\Framework\Shell\ComplexParameter;
use Laminas\Console\Request;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\Mvc\Application;
use Laminas\Mvc\MvcEvent;
use Laminas\Router\Http\RouteMatch;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Stdlib\RequestInterface;

/**
 * A listener that injects relevant Magento initialization parameters and initializes filesystem
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @codingStandardsIgnoreStart
 */
class InitParamListener implements ListenerAggregateInterface, FactoryInterface
{
    /**
     * A CLI parameter for injecting bootstrap variables
     */
    const BOOTSTRAP_PARAM = 'magento-init-params';

    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    private $listeners = [];


    /**
     * @inheritdoc
     *
     * The $priority argument is added to support latest versions of Zend Event Manager.
     * Starting from Zend Event Manager 3.0.0 release the ListenerAggregateInterface::attach()
     * supports the `priority` argument.
     *
     * @param EventManagerInterface $events
     * @param int                   $priority
     * @return void
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $sharedEvents = $events->getSharedManager();
        $sharedEvents->attach(
            Application::class,
            MvcEvent::EVENT_BOOTSTRAP,
            [$this, 'onBootstrap'],
            $priority
        );

        $this->listeners = $sharedEvents->getListeners([Application::class], MvcEvent::EVENT_BOOTSTRAP);
    }

    /**
     * @inheritdoc
     *
     * @param EventManagerInterface $events
     * @return void
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            $events->detach($listener);
            unset($this->listeners[$index]);
        }
    }

    /**
     * An event subscriber that initializes DirectoryList and Filesystem objects in ZF application bootstrap
     *
     * @param MvcEvent $e
     * @return void
     */
    public function onBootstrap(MvcEvent $e)
    {
        /** @var Application $application */
        $application = $e->getApplication();
        $initParams = $application->getServiceManager()->get(self::BOOTSTRAP_PARAM);
        $directoryList = $this->createDirectoryList($initParams);
        $serviceManager = $application->getServiceManager();
        $serviceManager->setService(\Magento\Framework\App\Filesystem\DirectoryList::class, $directoryList);
        $serviceManager->setService(\Magento\Framework\Filesystem::class, $this->createFilesystem($directoryList));
    }

    /**
     * @inheritdoc
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this->extractInitParameters($serviceLocator->get('Application'));
    }

    /**
     * Collects init params configuration from multiple sources
     *
     * Each next step overwrites previous, whenever data is available, in the following order:
     * 1: ZF application config
     * 2: environment
     * 3: CLI parameters (if the application is running in CLI mode)
     *
     * @param Application $application
     * @return array
     */
    private function extractInitParameters(Application $application)
    {
        $result = [];
        $config = $application->getConfig();
        if (isset($config[self::BOOTSTRAP_PARAM])) {
            $result = $config[self::BOOTSTRAP_PARAM];
        }
        foreach ([State::PARAM_MODE, AppBootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS] as $initKey) {
            if (isset($_SERVER[$initKey])) {
                $result[$initKey] = $_SERVER[$initKey];
            }
        }
        $result = array_replace_recursive($result, $this->extractFromCli($application->getRequest()));
        return $result;
    }

    /**
     * Extracts the directory paths from a CLI request
     *
     * Uses format of a URL query
     *
     * @param RequestInterface $request
     * @return array
     */
    private function extractFromCli(RequestInterface $request)
    {
        if (!($request instanceof Request)) {
            return [];
        }
        $bootstrapParam = new ComplexParameter(self::BOOTSTRAP_PARAM);
        foreach ($request->getContent() as $paramStr) {
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
    public function createDirectoryList($initParams)
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
    public function createFilesystem(DirectoryList $directoryList)
    {
        $driverPool = new Filesystem\DriverPool();
        return new Filesystem(
            $directoryList,
            new Filesystem\Directory\ReadFactory($driverPool),
            new Filesystem\Directory\WriteFactory($driverPool)
        );
    }
}
