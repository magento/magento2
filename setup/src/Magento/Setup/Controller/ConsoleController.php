<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Magento\Setup\Model\ConsoleLogger;
use Magento\Setup\Model\Installer;
use Magento\Setup\Model\InstallerFactory;
use Magento\Setup\Mvc\Bootstrap\InitParamListener;
use Zend\Console\Request as ConsoleRequest;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Magento\Setup\Model\ObjectManagerProvider;
use Symfony\Component\Console\Output\StreamOutput;

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
    /**#@- */

    /**
     * Map of controller actions exposed in CLI
     *
     * @var string[]
     */
    private static $actions = [
        self::CMD_HELP => 'help',
    ];

    /**
     * Options for "help" command
     *
     * @var string[]
     */
    private static $helpOptions = [
        self::CMD_INSTALL,
    ];

    /**
     * Logger
     *
     * @var ConsoleLogger
     */
    private $log;

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
        return [
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
     * @param Lists $options
     * @param InstallerFactory $installerFactory
     * @param ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(
        InstallerFactory $installerFactory,
        ObjectManagerProvider $objectManagerProvider
    ) {
        $this->objectManagerProvider = $objectManagerProvider;
        $stdOutput = fopen('php://stdout', 'w');
        $output = new StreamOutput($stdOutput);
        $this->log = $this->objectManagerProvider->get()->create(
            'Magento\Setup\Model\ConsoleLogger',
            ['output' => $output]
        );
        $this->installer = $installerFactory->create($this->log);

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
}
