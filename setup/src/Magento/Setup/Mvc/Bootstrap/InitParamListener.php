<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Mvc\Bootstrap;

use Magento\Framework\App\Bootstrap as AppBootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\Framework\Shell\ComplexParameter;
use Zend\Console\Request;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\RequestInterface;

/**
 * A listener that injects relevant Magento initialization parameters and initializes Magento\Filesystem component.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InitParamListener implements ListenerAggregateInterface, FactoryInterface
{
    /**
     * A CLI parameter for injecting bootstrap variables
     */
    const BOOTSTRAP_PARAM = 'magento-init-params';

    /**
     * List of ZF event listeners
     *
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    private $listeners = [];

    /**
     * List of controllers which should be skipped from auth check
     *
     * @var array
     */
    private $controllersToSkip = [
        'Magento\Setup\Controller\Session',
        'Magento\Setup\Controller\Install',
        'Magento\Setup\Controller\Success'

    ];

    /**
     * {@inheritdoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents = $events->getSharedManager();
        $this->listeners[] = $sharedEvents->attach(
            'Zend\Mvc\Application',
            MvcEvent::EVENT_BOOTSTRAP,
            [$this, 'onBootstrap']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
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
        $serviceManager->setService('Magento\Framework\App\Filesystem\DirectoryList', $directoryList);
        $serviceManager->setService('Magento\Framework\Filesystem', $this->createFilesystem($directoryList));

        if (!($application->getRequest() instanceof Request)) {
            $eventManager = $application->getEventManager();
            $eventManager->attach(MvcEvent::EVENT_DISPATCH, [$this, 'authPreDispatch'], 100);
        }
    }

    /**
     * Check if user login
     *
     * @param object $event
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function authPreDispatch($event)
    {
        /** @var RouteMatch $routeMatch */
        $routeMatch = $event->getRouteMatch();
        $controller = $routeMatch->getParam('controller');

        if (!in_array($controller, $this->controllersToSkip)) {
            /** @var Application $application */
            $application = $event->getApplication();
            $serviceManager = $application->getServiceManager();
            if ($serviceManager->get('Magento\Framework\App\DeploymentConfig')->isAvailable()) {
                $objectManagerProvider = $serviceManager->get('Magento\Setup\Model\ObjectManagerProvider');
                /** @var \Magento\Framework\ObjectManagerInterface $objectManager */
                $objectManager = $objectManagerProvider->get();
                /** @var \Magento\Framework\App\State $adminAppState */
                $adminAppState = $objectManager->get('Magento\Framework\App\State');
                $adminAppState->setAreaCode(\Magento\Framework\App\Area::AREA_ADMIN);
                $objectManager->create(
                    'Magento\Backend\Model\Auth\Session',
                    [
                        'sessionConfig' => $objectManager->get('Magento\Backend\Model\Session\AdminConfig'),
                        'appState' => $adminAppState
                    ]
                );

                if (!$objectManager->get('Magento\Backend\Model\Auth')->isLoggedIn()) {
                    $response = $event->getResponse();
                    $response->getHeaders()->addHeaderLine('Location', 'index.php/session/unlogin');
                    $response->setStatusCode(302);

                    $event->stopPropagation();
                    return $response;
                }
            }
        }
        return false;
    }

    /**
     * {@inheritdoc}
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
