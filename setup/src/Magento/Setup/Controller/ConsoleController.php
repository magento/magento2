<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Composer\Package\Version\VersionParser;
use Magento\Framework\App\MaintenanceMode;
use Magento\Setup\Model\AdminAccount;
use Magento\Setup\Model\ConsoleLogger;
use Magento\Setup\Model\DeploymentConfigMapper;
use Magento\Setup\Model\Installer;
use Magento\Setup\Model\InstallerFactory;
use Magento\Setup\Model\Lists;
use Magento\Setup\Model\UserConfigurationDataMapper as UserConfig;
use Magento\Setup\Mvc\Bootstrap\InitParamListener;
use Zend\Console\Request as ConsoleRequest;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Framework\Module\DbVersionInfo;

/**
 * Controller that handles all setup commands via command line interface.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConsoleController extends AbstractActionController
{
    /**#@+
     * Supported command types
     */
    const CMD_HELP = 'help';
    const CMD_INSTALL = 'install';
    const CMD_INSTALL_CONFIG = 'install-configuration';
    const CMD_INSTALL_SCHEMA = 'install-schema';
    const CMD_INSTALL_DATA = 'install-data';
    const CMD_INSTALL_USER_CONFIG = 'install-user-configuration';
    const CMD_INSTALL_ADMIN_USER = 'install-admin-user';
    const CMD_UPDATE = 'update';
    const CMD_DB_STATUS = 'db-status';
    const CMD_UNINSTALL = 'uninstall';
    const CMD_MAINTENANCE = 'maintenance';
    const CMD_MODULE_ENABLE = 'module-enable';
    const CMD_MODULE_DISABLE = 'module-disable';
    /**#@- */

    /**
     * Help option for retrieving list of modules
     */
    const HELP_LIST_OF_MODULES = 'module-list';

    /**
     * Map of controller actions exposed in CLI
     *
     * @var string[]
     */
    private static $actions = [
        self::CMD_HELP => 'help',
        self::CMD_INSTALL => 'install',
        self::CMD_INSTALL_CONFIG => 'installDeploymentConfig',
        self::CMD_INSTALL_SCHEMA => 'installSchema',
        self::CMD_INSTALL_DATA => 'installData',
        self::CMD_INSTALL_USER_CONFIG => 'installUserConfig',
        self::CMD_INSTALL_ADMIN_USER => 'installAdminUser',
        self::CMD_UPDATE => 'update',
        self::CMD_DB_STATUS => 'dbStatus',
        self::CMD_UNINSTALL => 'uninstall',
        self::CMD_MAINTENANCE => 'maintenance',
        self::CMD_MODULE_ENABLE => 'module',
        self::CMD_MODULE_DISABLE => 'module',
    ];

    /**
     * Options for "help" command
     *
     * @var string[]
     */
    private static $helpOptions = [
        self::CMD_INSTALL,
        self::CMD_INSTALL_CONFIG,
        self::CMD_INSTALL_SCHEMA,
        self::CMD_INSTALL_DATA,
        self::CMD_INSTALL_USER_CONFIG,
        self::CMD_INSTALL_ADMIN_USER,
        self::CMD_UPDATE,
        self::CMD_DB_STATUS,
        self::CMD_UNINSTALL,
        self::CMD_MAINTENANCE,
        self::CMD_MODULE_ENABLE,
        self::CMD_MODULE_DISABLE,
        UserConfig::KEY_LANGUAGE,
        UserConfig::KEY_CURRENCY,
        UserConfig::KEY_TIMEZONE,
        self::HELP_LIST_OF_MODULES,
    ];

    /**
     * Logger
     *
     * @var ConsoleLogger
     */
    private $log;

    /**
     * Options Lists
     *
     * @var Lists
     */
    private $options;

    /**
     * Installer service
     *
     * @var Installer
     */
    private $installer;

    /**
     * Object manager provider
     *
     * @var ObjectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * Gets router configuration to be used in module definition
     *
     * @return array
     */
    public static function getRouterConfig()
    {
        $result = [];
        $config = self::getCliConfig();
        foreach (self::$actions as $type => $action) {
            $result[$type] = ['options' => [
                'route' => $config[$type]['route'],
                'defaults' => ['controller' => __CLASS__, 'action' => $action],
            ]];
        }
        return $result;
    }

    /**
     * Gets console usage to be used in module definition
     *
     * @return array
     */
    public static function getConsoleUsage()
    {
        $result = ["Usage:\n"];
        foreach (self::getCliConfig() as $cmd) {
            $result[$cmd['usage_short']] = $cmd['usage_desc'];
        }
        foreach (self::$helpOptions as $type) {
            $result[] = '    ' . ConsoleController::CMD_HELP . ' ' . $type;
        }
        return $result;
    }

    /**
     * Gets command usage
     *
     * @return array
     */
    public static function getCommandUsage()
    {
        $result = [];
        foreach (self::getCliConfig() as $key => $cmd) {
            $result[$key] = $cmd['usage'];
        }
        return $result;
    }

    /**
     * The CLI that this controller implements
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private static function getCliConfig()
    {
        $deployConfig = '--' . DeploymentConfigMapper::KEY_DB_HOST . '='
            . ' --' . DeploymentConfigMapper::KEY_DB_NAME . '='
            . ' --' . DeploymentConfigMapper::KEY_DB_USER . '='
            . ' --' . DeploymentConfigMapper::KEY_BACKEND_FRONTNAME . '='
            . ' [--' . DeploymentConfigMapper::KEY_DB_PASS . '=]'
            . ' [--' . DeploymentConfigMapper::KEY_DB_PREFIX . '=]'
            . ' [--' . DeploymentConfigMapper::KEY_DB_MODEL . '=]'
            . ' [--' . DeploymentConfigMapper::KEY_DB_INIT_STATEMENTS . '=]'
            . ' [--' . DeploymentConfigMapper::KEY_SESSION_SAVE . '=]'
            . ' [--' . DeploymentConfigMapper::KEY_ENCRYPTION_KEY . '=]'
            . ' [--' . Installer::ENABLE_MODULES . '=]'
            . ' [--' . Installer::DISABLE_MODULES . '=]';
        $userConfig = '[--' . UserConfig::KEY_BASE_URL . '=]'
            . ' [--' . UserConfig::KEY_LANGUAGE . '=]'
            . ' [--' . UserConfig::KEY_TIMEZONE . '=]'
            . ' [--' . UserConfig::KEY_CURRENCY . '=]'
            . ' [--' . UserConfig::KEY_USE_SEF_URL . '=]'
            . ' [--' . UserConfig::KEY_IS_SECURE . '=]'
            . ' [--' . UserConfig::KEY_BASE_URL_SECURE . '=]'
            . ' [--' . UserConfig::KEY_IS_SECURE_ADMIN . '=]'
            . ' [--' . UserConfig::KEY_ADMIN_USE_SECURITY_KEY . '=]';
        $adminUser = '--' . AdminAccount::KEY_USERNAME . '='
            . ' --' . AdminAccount::KEY_PASSWORD . '='
            . ' --' . AdminAccount::KEY_EMAIL . '='
            . ' --' . AdminAccount::KEY_FIRST_NAME . '='
            . ' --' . AdminAccount::KEY_LAST_NAME . '=';
        $salesConfig = '[--' . Installer::SALES_ORDER_INCREMENT_PREFIX . '=]';
        return [
            self::CMD_INSTALL => [
                'route' => self::CMD_INSTALL
                    . " {$deployConfig} {$userConfig} {$adminUser} {$salesConfig}"
                    . ' [--' . Installer::CLEANUP_DB . ']'
                    . ' [--' . Installer::USE_SAMPLE_DATA . '=]',
                'usage' => "{$deployConfig} {$userConfig} {$adminUser} {$salesConfig}"
                    . ' [--' . Installer::CLEANUP_DB . ']'
                    . ' [--' . Installer::USE_SAMPLE_DATA . '=]',
                'usage_short' => self::CMD_INSTALL . ' <options>',
                'usage_desc' => 'Install Magento application',
            ],
            self::CMD_UPDATE => [
                'route' => self::CMD_UPDATE,
                'usage' => '',
                'usage_short' => self::CMD_UPDATE,
                'usage_desc' => 'Update database schema and data',
            ],
            self::CMD_DB_STATUS => [
                'route' => self::CMD_DB_STATUS,
                'usage' => '',
                'usage_short' => self::CMD_DB_STATUS,
                'usage_desc' => 'Check if update of DB schema or data is required',
            ],
            self::CMD_UNINSTALL => [
                'route' => self::CMD_UNINSTALL,
                'usage' => '',
                'usage_short' => self::CMD_UNINSTALL,
                'usage_desc' => 'Uninstall Magento application',
            ],
            self::CMD_INSTALL_CONFIG => [
                'route' => self::CMD_INSTALL_CONFIG . ' ' . $deployConfig,
                'usage' => $deployConfig,
                'usage_short' => self::CMD_INSTALL_CONFIG . ' <options>',
                'usage_desc' => 'Install deployment configuration',
            ],
            self::CMD_INSTALL_SCHEMA => [
                'route' => self::CMD_INSTALL_SCHEMA,
                'usage' => '',
                'usage_short' => self::CMD_INSTALL_SCHEMA,
                'usage_desc' => 'Install DB schema',
            ],
            self::CMD_INSTALL_DATA => [
                'route' => self::CMD_INSTALL_DATA,
                'usage' => '',
                'usage_short' => self::CMD_INSTALL_DATA,
                'usage_desc' => 'Install data fixtures',
            ],
            self::CMD_INSTALL_USER_CONFIG => [
                'route' => self::CMD_INSTALL_USER_CONFIG . ' ' . $userConfig,
                'usage' => $userConfig,
                'usage_short' => self::CMD_INSTALL_USER_CONFIG . ' <options>',
                'usage_desc' => 'Install user configuration',
            ],
            self::CMD_INSTALL_ADMIN_USER => [
                'route' => self::CMD_INSTALL_ADMIN_USER . ' ' . $adminUser,
                'usage' => $adminUser,
                'usage_short' => self::CMD_INSTALL_ADMIN_USER . ' <options>',
                'usage_desc' => 'Install admin user account',
            ],
            self::CMD_MAINTENANCE => [
                'route' => self::CMD_MAINTENANCE . ' [--set=] [--addresses=]',
                'usage' => '[--set=1|0] [--addresses=127.0.0.1,...|none]',
                'usage_short' => self::CMD_MAINTENANCE,
                'usage_desc' => 'Set maintenance mode, optionally for specified addresses',
            ],
            self::CMD_MODULE_ENABLE => [
                'route' => self::CMD_MODULE_ENABLE . ' --modules= [--force]',
                'usage' => '--modules=Module_Foo,Module_Bar [--force]',
                'usage_short' => self::CMD_MODULE_ENABLE,
                'usage_desc' => 'Enable specified modules'
            ],
            self::CMD_MODULE_DISABLE => [
                'route' => self::CMD_MODULE_DISABLE . ' --modules= [--force]',
                'usage' => '--modules=Module_Foo,Module_Bar [--force]',
                'usage_short' => self::CMD_MODULE_DISABLE,
                'usage_desc' => 'Disable specified modules'
            ],
            self::CMD_HELP => [
                'route' => self::CMD_HELP . ' [' . implode('|', self::$helpOptions) . ']:type',
                'usage' => '<' . implode('|', self::$helpOptions) . '>',
                'usage_short' => self::CMD_HELP . ' <topic>',
                'usage_desc' => 'Help about particular command or topic:',
            ],
        ];
    }

    /**
     * Constructor
     *
     * @param ConsoleLogger $consoleLogger
     * @param Lists $options
     * @param InstallerFactory $installerFactory
     * @param MaintenanceMode $maintenanceMode
     * @param ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(
        ConsoleLogger $consoleLogger,
        Lists $options,
        InstallerFactory $installerFactory,
        MaintenanceMode $maintenanceMode,
        ObjectManagerProvider $objectManagerProvider
    ) {
        $this->log = $consoleLogger;
        $this->options = $options;
        $this->installer = $installerFactory->create($consoleLogger);
        $this->maintenanceMode = $maintenanceMode;
        $this->objectManagerProvider = $objectManagerProvider;
        // By default we use our customized error handler, but for CLI we want to display all errors
        restore_error_handler();
    }

    /**
     * Adding Check for Allowing only console application to come through
     *
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function setEventManager(EventManagerInterface $events)
    {
        parent::setEventManager($events);
        $controller = $this;
        $events->attach('dispatch', function ($action) use ($controller) {
            /** @var $action \Zend\Mvc\Controller\AbstractActionController */
            // Make sure that we are running in a console and the user has not tricked our
            // application into running this action from a public web server.
            if (!$action->getRequest() instanceof ConsoleRequest) {
                throw new \RuntimeException('You can only use this action from a console!');
            }
        }, 100); // execute before executing action logic
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        try {
            return parent::onDispatch($e);
        } catch (\Magento\Setup\Exception $exception) {
            $this->log->log($exception->getMessage());
            return $this->getResponse();
        }
    }

    /**
     * Controller for Install Command
     *
     * @return void
     */
    public function installAction()
    {
        /** @var \Zend\Console\Request $request */
        $request = $this->getRequest();
        $this->installer->install($request->getParams());
    }

    /**
     * Creates the config.php file
     *
     * @return void
     */
    public function installDeploymentConfigAction()
    {
        /** @var \Zend\Console\Request $request */
        $request = $this->getRequest();
        $this->installer->checkInstallationFilePermissions();
        $this->installer->installDeploymentConfig($request->getParams());
    }

    /**
     * Installs and updates database schema
     *
     * @return void
     */
    public function installSchemaAction()
    {
        $this->installer->installSchema();
    }

    /**
     * Installs and updates data fixtures
     *
     * @return void
     */
    public function installDataAction()
    {
        $this->installer->installDataFixtures();
    }

    /**
     * Updates database schema and data
     *
     * @return void
     */
    public function updateAction()
    {
        $this->installer->updateModulesSequence();
        $this->installer->installSchema();
        $this->installer->installDataFixtures();
    }

    /**
     * Checks if DB schema or data upgrade is required
     *
     * @return void
     */
    public function dbStatusAction()
    {
        /** @var DbVersionInfo $dbVersionInfo */
        $dbVersionInfo = $this->objectManagerProvider->get()
            ->get('Magento\Framework\Module\DbVersionInfo');
        $outdated = $dbVersionInfo->getDbVersionErrors();
        if (!empty($outdated)) {
            $this->log->log("The module code base doesn't match the DB schema and data.");
            $versionParser = new VersionParser();
            $codebaseUpdateNeeded = false;
            foreach ($outdated as $row) {
                if (!$codebaseUpdateNeeded && $row[DbVersionInfo::KEY_CURRENT] !== 'none') {
                    // check if module code base update is needed
                    $currentVersion = $versionParser->parseConstraints($row[DbVersionInfo::KEY_CURRENT]);
                    $requiredVersion = $versionParser->parseConstraints('>' . $row[DbVersionInfo::KEY_REQUIRED]);
                    if ($requiredVersion->matches($currentVersion)) {
                        $codebaseUpdateNeeded = true;
                    };
                }
                $this->log->log(sprintf(
                    "%20s %10s: %11s  ->  %-11s",
                    $row[DbVersionInfo::KEY_MODULE],
                    $row[DbVersionInfo::KEY_TYPE],
                    $row[DbVersionInfo::KEY_CURRENT],
                    $row[DbVersionInfo::KEY_REQUIRED]
                ));
            }
            if ($codebaseUpdateNeeded) {
                $this->log->log(
                    'Some modules use code versions newer or older than the database. ' .
                    'First update the module code, then run the "Update" command.'
                );
            } else {
                $this->log->log('Run the "Update" command to update your DB schema and data');
            }
        } else {
            $this->log->log('All modules are up to date');
        }
    }

    /**
     * Installs user configuration
     *
     * @return void
     */
    public function installUserConfigAction()
    {
        /** @var \Zend\Console\Request $request */
        $request = $this->getRequest();
        $this->installer->installUserConfig($request->getParams());
    }

    /**
     * Installs admin user
     *
     * @return void
     */
    public function installAdminUserAction()
    {
        /** @var \Zend\Console\Request $request */
        $request = $this->getRequest();
        $this->installer->installAdminUser($request->getParams());
    }

    /**
     * Controller for Uninstall Command
     *
     * @return void
     */
    public function uninstallAction()
    {
        $this->installer->uninstall();
    }

    /**
     * Action for "maintenance" command
     *
     * @return void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     */
    public function maintenanceAction()
    {
        /** @var \Zend\Console\Request $request */
        $request = $this->getRequest();
        $set = $request->getParam('set');
        $addresses = $request->getParam('addresses');

        if (null !== $set) {
            if (1 == $set) {
                $this->log->log('Enabling maintenance mode...');
                $this->maintenanceMode->set(true);
            } else {
                $this->log->log('Disabling maintenance mode...');
                $this->maintenanceMode->set(false);
            }
        }
        if (null !== $addresses) {
            $addresses = ('none' == $addresses) ? '' : $addresses;
            $this->maintenanceMode->setAddresses($addresses);
        }

        $this->log->log('Status: maintenance mode is ' . ($this->maintenanceMode->isOn() ? 'active' : 'not active'));
        $addressInfo = $this->maintenanceMode->getAddressInfo();
        if (!empty($addressInfo)) {
            $addresses = implode(', ', $addressInfo);
            $this->log->log('List of exempt IP-addresses: ' . ($addresses ? $addresses : 'none'));
        }
    }

    /**
     * Action for enabling or disabling modules
     *
     * @return void
     * @throws \Magento\Setup\Exception
     */
    public function moduleAction()
    {
        /** @var \Zend\Console\Request $request */
        $request = $this->getRequest();
        $isEnable = $request->getParam(0) == self::CMD_MODULE_ENABLE;
        $modules = explode(',', $request->getParam('modules'));
        /** @var \Magento\Framework\Module\Status $status */
        $status = $this->objectManagerProvider->get()->create('Magento\Framework\Module\Status');

        $modulesToChange = $status->getModulesToChange($isEnable, $modules);
        $message = '';
        if (!empty($modulesToChange)) {
            if (!$request->getParam('force')) {
                $constraints = $status->checkConstraints($isEnable, $modulesToChange);
                if ($constraints) {
                    $message .= "Unable to change status of modules because of the following constraints:\n"
                        . implode("\n", $constraints);
                    throw new \Magento\Setup\Exception($message);
                }
            } else {
                $message .= 'Alert: Your store may not operate properly because of '
                    . "dependencies and conflicts of this module(s).\n";
            }
            $status->setIsEnabled($isEnable, $modulesToChange);
            $updateAfterEnableMessage = '';
            if ($isEnable) {
                $message .= 'The following modules have been enabled:';
                $updateAfterEnableMessage = "\nTo make sure that the enabled modules are properly registered,"
                    . " run 'update' command.";
            } else {
                $message .= 'The following modules have been disabled:';
            }
            $message .= ' ' . implode(', ', $modulesToChange) . $updateAfterEnableMessage;
        } else {
            $message .= 'There have been no changes to any modules.';
        }
        $this->log->log($message);
    }

    /**
     * Shows necessary information for installing Magento
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function helpAction()
    {
        $type = $this->getRequest()->getParam('type');
        if ($type === false) {
            $usageInfo = $this->formatConsoleFullUsageInfo(
                array_merge(self::getConsoleUsage(), InitParamListener::getConsoleUsage())
            );
            return $usageInfo;
        }
        switch($type) {
            case UserConfig::KEY_LANGUAGE:
                return $this->arrayToString($this->options->getLocaleList());
            case UserConfig::KEY_CURRENCY:
                return $this->arrayToString($this->options->getCurrencyList());
            case UserConfig::KEY_TIMEZONE:
                return $this->arrayToString($this->options->getTimezoneList());
            case self::HELP_LIST_OF_MODULES:
                return $this->getModuleListMsg();
            default:
                $usages = self::getCommandUsage();
                if (isset($usages[$type])) {
                    if ($usages[$type]) {
                        $formatted = $this->formatCliUsage($usages[$type]);
                        return "\nAvailable parameters:\n{$formatted}\n";
                    }
                    return "\nThis command has no parameters.\n";
                }
                throw new \InvalidArgumentException("Unknown type: {$type}");
        }
    }

    /**
     * Formats full usage info for console when user inputs 'help' command with no type
     *
     * @param array $usageInfo
     * @return string
     */
    private function formatConsoleFullUsageInfo($usageInfo)
    {
        $result = "\n==-------------------==\n"
            . "   Magento Setup CLI   \n"
            . "==-------------------==\n";
        $mask = "%-50s %-30s\n";
        $script = 'index.php';
        foreach ($usageInfo as $key => $value) {
            if ($key === 0) {
                $result .= sprintf($mask, "\n$value", '');
            } elseif (is_numeric($key)) {
                if (is_array($value)) {
                    $result .= sprintf($mask, "  " . $value[0], $value[1]);
                } else {
                    $result .= sprintf($mask, '', $value);
                }
            } else {
                $result .= sprintf($mask, "  $script " . $key, $value);
            }
        }
        return $result;
    }

    /**
     * Formats output of "usage" into more readable format by grouping required/optional parameters and wordwrapping
     *
     * @param string $text
     * @return string
     */
    private function formatCliUsage($text)
    {
        $result = ['required' => [], 'optional' => []];
        foreach (explode(' ', $text) as $value) {
            if (empty($value)) {
                continue;
            }
            if (strpos($value, '[') === 0) {
                $group = 'optional';
            } else {
                $group = 'required';
            }
            $result[$group][] = $value;
        }

        return wordwrap(implode(' ', $result['required']) . "\n" . implode(' ', $result['optional']), 120);
    }

    /**
     * Convert an array to string
     *
     * @param array $input
     * @return string
     */
    private function arrayToString($input)
    {
        $result = '';
        foreach ($input as $key => $value) {
            $result .= "$key => $value\n";
        }
        return $result;
    }

    /**
     * Get formatted message containing list of enabled and disabled modules
     *
     * @return string
     */
    private function getModuleListMsg()
    {
        $moduleList = $this->objectManagerProvider->get()->create('Magento\Framework\Module\ModuleList');
        $result = "\nList of enabled modules:\n";
        $enabledModuleList = $moduleList->getNames();
        foreach ($enabledModuleList as $moduleName) {
            $result .= "$moduleName\n";
        }
        if (count($enabledModuleList) === 0) {
            $result .= "None\n";
        }

        $fullModuleList = $this->objectManagerProvider->get()->create('Magento\Framework\Module\FullModuleList');
        $result .= "\nList of disabled modules:\n";
        $disabledModuleList = array_diff($fullModuleList->getNames(), $enabledModuleList);
        foreach ($disabledModuleList as $moduleName) {
            $result .= "$moduleName\n";
        }
        if (count($disabledModuleList) === 0) {
            $result .= "None\n";
        }

        return $result;
    }
}
