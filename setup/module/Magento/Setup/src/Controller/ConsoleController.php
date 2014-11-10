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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Setup\Controller;

use Magento\Locale\Lists;
use Magento\Setup\Module\Setup\Config;
use Magento\Setup\Model\InstallerFactory;
use Magento\Setup\Model\Installer;
use Magento\Setup\Model\ConsoleLogger;
use Zend\Console\Request as ConsoleRequest;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Magento\Setup\Model\UserConfigurationData as UserConfig;
use Magento\Setup\Model\AdminAccount;
use Magento\Framework\App\MaintenanceMode;

/**
 * Controller that handles all setup commands via command line interface.
 *
 * @package Magento\Setup\Controller
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
    const CMD_UNINSTALL = 'uninstall';
    const CMD_MAINTENANCE = 'maintenance';
    /**#@- */

    /**#@+
     * Additional keys for "info" command
     */
    const INFO_LOCALES = 'languages';
    const INFO_CURRENCIES = 'currencies';
    const INFO_TIMEZONES = 'timezones';
    /**#@- */

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
        self::CMD_UNINSTALL => 'uninstall',
        self::CMD_MAINTENANCE => 'maintenance',
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
        self::CMD_UNINSTALL,
        self::CMD_MAINTENANCE,
        self::INFO_LOCALES,
        self::INFO_CURRENCIES,
        self::INFO_TIMEZONES,
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
     * The CLI that this controller implements
     *
     * @return array
     */
    private static function getCliConfig()
    {
        $deployConfig = '--' . Config::KEY_DB_HOST . '='
            . ' --' . Config::KEY_DB_NAME . '='
            . ' --' . Config::KEY_DB_USER . '='
            . ' --' . Config::KEY_BACKEND_FRONTNAME . '='
            . ' [--' . Config::KEY_DB_PASS . '=]'
            . ' [--' . Config::KEY_DB_PREFIX . '=]'
            . ' [--' . Config::KEY_DB_MODEL . '=]'
            . ' [--' . Config::KEY_DB_INIT_STATEMENTS . '=]'
            . ' [--' . Config::KEY_SESSION_SAVE . '=]'
            . ' [--' . Config::KEY_ENCRYPTION_KEY . '=]';
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
                    . ' [--' . Installer::CLEANUP_DB . ']',
                'usage' => "{$deployConfig} {$userConfig} {$adminUser} {$salesConfig}"
                    . ' [--' . Installer::CLEANUP_DB . ']',
                'usage_short' => self::CMD_INSTALL . ' <options>',
                'usage_desc' => 'Install Magento application',
            ],
            self::CMD_UPDATE => [
                'route' => self::CMD_UPDATE,
                'usage' => '',
                'usage_short' => self::CMD_UPDATE,
                'usage_desc' => 'Update database schema and data',
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
            self::CMD_HELP => [
                'route' => self::CMD_HELP . ' (' . implode('|', self::$helpOptions) . '):type',
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
     */
    public function __construct(
        ConsoleLogger $consoleLogger,
        Lists $options,
        InstallerFactory $installerFactory,
        MaintenanceMode $maintenanceMode
    ) {
        $this->log = $consoleLogger;
        $this->options = $options;
        $this->installer = $installerFactory->create($consoleLogger);
        $this->maintenanceMode = $maintenanceMode;
    }

    /**
     * Adding Check for Allowing only console application to come through
     *
     * {@inheritdoc}
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
     * Controller for Install Command
     *
     * @return void
     * @throws \Exception
     */
    public function installAction()
    {
        /** @var \Zend\Console\Request $request */
        $request = $this->getRequest();
        $this->installer->install($request->getParams());
    }

    /**
     * Creates the local.xml file
     *
     * @return void
     * @throws \Exception
     */
    public function installDeploymentConfigAction()
    {
        $this->installer->checkInstallationFilePermissions();
        /** @var \Zend\Console\Request $request */
        $request = $this->getRequest();
        $this->installer->installDeploymentConfig($request->getParams());
    }

    /**
     * Installs and updates database schema
     *
     * @return void
     * @throws \Exception
     */
    public function installSchemaAction()
    {
        $this->installer->installSchema();
    }

    /**
     * Installs and updates data fixtures
     *
     * @return void
     * @throws \Exception
     */
    public function installDataAction()
    {
        $this->installer->checkInstallationFilePermissions();
        $this->installer->installDataFixtures();
    }

    /**
     * Updates database schema and data
     *
     * @return void
     * @throws \Exception
     */
    public function updateAction()
    {
        $this->installer->checkInstallationFilePermissions();
        $this->installer->installSchema();
        $this->installer->installDataFixtures();
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
     * @throws \Exception
     */
    public function uninstallAction()
    {
        $this->installer->uninstall();
    }

    /**
     * Action for "maintenance" command
     *
     * @return void
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
        $addresses = implode(', ', $this->maintenanceMode->getAddressInfo());
        $this->log->log('List of exempt IP-addresses: ' . ($addresses ? $addresses : 'none'));
    }

    /**
     * Shows necessary information for installing Magento
     *
     * @return string
     * @throws \Exception
     */
    public function helpAction()
    {
        $type = $this->getRequest()->getParam('type');
        $details = self::getCliConfig();
        switch($type) {
            case self::INFO_LOCALES:
                return $this->arrayToString($this->options->getLocaleList());
            case self::INFO_CURRENCIES:
                return $this->arrayToString($this->options->getCurrencyList());
            case self::INFO_TIMEZONES:
                return $this->arrayToString($this->options->getTimezoneList());
            default:
                if (isset($details[$type])) {
                    if ($details[$type]['usage']) {
                        $formatted = $this->formatCliUsage($details[$type]['usage']);
                        return "\nAvailable parameters:\n{$formatted}\n";
                    }
                    return "\nThis command has no parameters.\n";
                }
                throw new \InvalidArgumentException("Unknown type: {$type}");
        }
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
        foreach (explode(' ', $text) as  $value) {
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
}
