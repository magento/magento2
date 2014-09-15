<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\App;

use Magento\Framework\Profiler;
use \Magento\Framework\AppInterface;

/**
 * A bootstrap of Magento application
 *
 * Performs basic initialization root function: injects init parameters and creates object manager
 * Can create/run applications
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Bootstrap
{
    /**#+
     * Possible errors that can be triggered by the bootstrap
     */
    const ERR_MAINTENANCE = 901;
    const ERR_IS_INSTALLED = 902;
    /**#- */

    /**#+
     * Initialization parameters that allow control bootstrap behavior of asserting maintenance mode or is installed
     *
     * Possible values:
     * - true -- set expectation that it is required
     * - false -- set expectation that is required not to
     * - null -- bypass the assertion completely
     *
     * If key is absent in the parameters array, the default behavior will be used
     * @see DEFAULT_REQUIRE_MAINTENANCE
     * @see DEFAULT_REQUIRE_IS_INSTALLED
     */
    const PARAM_REQUIRE_MAINTENANCE = 'MAGE_REQUIRE_MAINTENANCE';
    const PARAM_REQUIRE_IS_INSTALLED = 'MAGE_REQUIRE_IS_INSTALLED';
    /**#- */

    /**#+
     * Default behavior of bootstrap assertions
     */
    const DEFAULT_REQUIRE_MAINTENANCE = false;
    const DEFAULT_REQUIRE_IS_INSTALLED = true;
    /**#- */

    /**
     * The initialization parameters (normally come from the $_SERVER)
     *
     * @var array
     */
    private $server;

    /**
     * Root directory
     *
     * @var string
     */
    private $rootDir;

    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManager
     */
    private $objectManager;

    /**
     * Directory list
     *
     * @var Filesystem\DirectoryList
     */
    private $dirList;

    /**
     * Configuration directory
     *
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    private $configDir;

    /**
     * Maintenance mode manager
     *
     * @var \Magento\Framework\App\MaintenanceMode
     */
    private $maintenance;

    /**
     * Bootstrap-specific error code that may have been set in runtime
     *
     * @var int
     */
    private $errorCode = 0;

    /**
     * Attribute for creating object manager
     *
     * @var ObjectManagerFactory
     */
    private $factory;

    /**
     * Static method so that client code does not have to create Object Manager Factory every time Bootstrap is called
     *
     * @param string $rootDir
     * @param array $initParams
     * @param ObjectManagerFactory $factory
     * @return Bootstrap
     */
    public static function create($rootDir, array $initParams, ObjectManagerFactory $factory = null)
    {
        if ($factory === null) {
            $factory = new ObjectManagerFactory;
        }
        return new self($factory, $rootDir, $initParams);
    }

    /**
     * Constructor
     *
     * @param ObjectManagerFactory $factory
     * @param string $rootDir
     * @param array $initParams
     */
    public function __construct(ObjectManagerFactory $factory, $rootDir, array $initParams)
    {
        $this->factory = $factory;
        $this->rootDir = $rootDir;
        $this->server = $initParams;
    }

    /**
     * Gets the current parameters
     *
     * @return array
     */
    public function getParams()
    {
        return $this->server;
    }

    /**
     * Factory method for creating application instances
     *
     * @param string $type
     * @param array $arguments
     * @return \Magento\Framework\AppInterface
     * @throws \InvalidArgumentException
     */
    public function createApplication($type, $arguments = [])
    {
        try {
            $this->init();
            $application = $this->objectManager->create($type, $arguments);
            if (!($application instanceof AppInterface)) {
                throw new \InvalidArgumentException("The provided class doesn't implement AppInterface: {$type}");
            }
            return $application;
        } catch (\Exception $e) {
            $this->terminate($e);
        }
    }

    /**
     * Runs an application
     *
     * @param \Magento\Framework\AppInterface $application
     * @return void
     */
    public function run(AppInterface $application)
    {
        try {
            try {
                \Magento\Framework\Profiler::start('magento');
                $this->initErrorHandler();
                $this->init();
                $this->assertMaintenance();
                $this->assertInstalled();
                $response = $application->launch();
                $response->sendResponse();
                \Magento\Framework\Profiler::stop('magento');
            } catch (\Exception $e) {
                \Magento\Framework\Profiler::stop('magento');
                if (!$application->catchException($this, $e)) {
                    throw $e;
                }
            }
        } catch (\Exception $e) {
            $this->terminate($e);
        }
    }

    /**
     * Asserts maintenance mode
     *
     * @return void
     * @throws \Exception
     */
    protected function assertMaintenance()
    {
        $isExpected = $this->getIsExpected(self::PARAM_REQUIRE_MAINTENANCE, self::DEFAULT_REQUIRE_MAINTENANCE);
        if (null === $isExpected) {
            return;
        }
        $this->init();
        $isOn = $this->maintenance->isOn(isset($this->server['REMOTE_ADDR']) ? $this->server['REMOTE_ADDR'] : '');
        if ($isOn && !$isExpected) {
            $this->errorCode = self::ERR_MAINTENANCE;
            throw new \Exception('Unable to proceed: the maintenance mode is enabled.');
        }
        if (!$isOn && $isExpected) {
            $this->errorCode = self::ERR_MAINTENANCE;
            throw new \Exception('Unable to proceed: the maintenance mode must be enabled first.');
        }
    }

    /**
     * Asserts whether application is installed
     *
     * @return void
     * @throws \Exception
     */
    protected function assertInstalled()
    {
        $isExpected = $this->getIsExpected(self::PARAM_REQUIRE_IS_INSTALLED, self::DEFAULT_REQUIRE_IS_INSTALLED);
        if (null === $isExpected) {
            return;
        }
        $this->init();
        $isInstalled = $this->isInstalled();
        if (!$isInstalled && $isExpected) {
            $this->errorCode = self::ERR_IS_INSTALLED;
            throw new \Exception('Application is not installed yet.');
        }
        if ($isInstalled && !$isExpected) {
            $this->errorCode = self::ERR_IS_INSTALLED;
            throw new \Exception('Application is already installed.');
        }
    }

    /**
     * Analyze a key in the initialization parameters as "is expected" parameter
     *
     * If there is no such key, returns default value. Otherwise casts it to boolean, unless it is null
     *
     * @param string $key
     * @param bool $default
     * @return bool|null
     */
    private function getIsExpected($key, $default)
    {
        if (array_key_exists($key, $this->server)) {
            if (isset($this->server[$key])) {
                return (bool)(int)$this->server[$key];
            }
            return null;
        }
        return $default;
    }

    /**
     * Determines whether application is installed
     *
     * @return bool
     */
    private function isInstalled()
    {
        $this->init();
        return $this->configDir->isExist('local.xml');
    }

    /**
     * Gets the object manager instance
     *
     * @return \Magento\Framework\ObjectManager
     */
    public function getObjectManager()
    {
        $this->init();
        return $this->objectManager;
    }

    /**
     * Gets the directory list instance
     *
     * @return Filesystem\DirectoryList
     */
    public function getDirList()
    {
        $this->init();
        return $this->dirList;
    }

    /**
     * Sets a custom error handler
     *
     * @return void
     */
    private function initErrorHandler()
    {
        $handler = new ErrorHandler;
        set_error_handler([$handler, 'handler']);
    }

    /**
     * Initializes the essential objects
     *
     * @return void
     */
    private function init()
    {
        if (!$this->objectManager) {
            $this->objectManager = $this->factory->create($this->rootDir, $this->server);
            $this->dirList = $this->objectManager->get('Magento\Framework\App\Filesystem\DirectoryList');
            $this->maintenance = $this->objectManager->get('Magento\Framework\App\MaintenanceMode');
            /** @var $fileSystem \Magento\Framework\App\Filesystem */
            $fileSystem = $this->objectManager->get('Magento\Framework\App\Filesystem');
            $this->configDir = $fileSystem->getDirectoryRead(Filesystem::CONFIG_DIR);
        }
    }

    /**
     * Getter for error code
     *
     * @return int
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * Checks whether developer mode is set in the initialization parameters
     *
     * @return bool
     */
    public function isDeveloperMode()
    {
        return isset($this->server[State::PARAM_MODE]) && $this->server[State::PARAM_MODE] == State::MODE_DEVELOPER;
    }

    /**
     * Display an exception and terminate program execution
     *
     * @param \Exception $e
     * @return void
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    protected function terminate(\Exception $e)
    {
        if ($this->isDeveloperMode()) {
            echo $e;
        } else {
            $message = "An error has happened during application run. See debug log for details.\n";
            try {
                if (!$this->objectManager) {
                    throw new \DomainException();
                }
                $this->objectManager->get('Magento\Framework\Logger')->logException($e);
            } catch (\Exception $e) {
                $message .= "Could not write error message to log. Please use developer mode to see the message.\n";
            }
            echo $message;
        }
        exit(1);
    }
}
