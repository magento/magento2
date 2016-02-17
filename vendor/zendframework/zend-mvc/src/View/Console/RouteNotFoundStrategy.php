<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\View\Console;

use Zend\Console\Adapter\AdapterInterface as ConsoleAdapter;
use Zend\Console\ColorInterface;
use Zend\Console\Response as ConsoleResponse;
use Zend\Console\Request as ConsoleRequest;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\ModuleManager\ModuleManagerInterface;
use Zend\ModuleManager\Feature\ConsoleBannerProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Mvc\Application;
use Zend\Mvc\Exception\RuntimeException;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\Stdlib\ResponseInterface as Response;
use Zend\Stdlib\StringUtils;
use Zend\Text\Table;
use Zend\Version\Version;
use Zend\View\Model\ConsoleModel;

class RouteNotFoundStrategy extends AbstractListenerAggregate
{
    /**
     * Whether or not to display the reason for routing failure
     *
     * @var bool
     */
    protected $displayNotFoundReason = true;

    /**
     * The reason for a not-found condition
     *
     * @var bool|string
     */
    protected $reason = false;

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'handleRouteNotFoundError'));
    }

    /**
     * Set flag indicating whether or not to display the routing failure
     *
     * @param  bool $displayNotFoundReason
     * @return RouteNotFoundStrategy
     */
    public function setDisplayNotFoundReason($displayNotFoundReason)
    {
        $this->displayNotFoundReason = (bool) $displayNotFoundReason;
        return $this;
    }

    /**
     * Do we display the routing failure?
     *
     * @return bool
     */
    public function displayNotFoundReason()
    {
        return $this->displayNotFoundReason;
    }

    /**
     * Detect if an error is a route not found condition
     *
     * If a "controller not found" or "invalid controller" error type is
     * encountered, sets the response status code to 404.
     *
     * @param  MvcEvent $e
     * @throws RuntimeException
     * @throws ServiceNotFoundException
     * @return void
     */
    public function handleRouteNotFoundError(MvcEvent $e)
    {
        $error = $e->getError();
        if (empty($error)) {
            return;
        }

        $response = $e->getResponse();
        $request  = $e->getRequest();

        switch ($error) {
            case Application::ERROR_CONTROLLER_NOT_FOUND:
            case Application::ERROR_CONTROLLER_INVALID:
            case Application::ERROR_ROUTER_NO_MATCH:
                $this->reason = $error;
                if (!$response) {
                    $response = new ConsoleResponse();
                    $e->setResponse($response);
                }
                $response->setMetadata('error', $error);
                break;
            default:
                return;
        }

        $result = $e->getResult();
        if ($result instanceof Response) {
            // Already have a response as the result
            return;
        }

        // Prepare Console View Model
        $model = new ConsoleModel();
        $model->setErrorLevel(1);

        // Fetch service manager
        $sm = $e->getApplication()->getServiceManager();

        // Try to fetch module manager
        $mm = null;
        try {
            $mm = $sm->get('ModuleManager');
        } catch (ServiceNotFoundException $exception) {
            // The application does not have or use module manager, so we cannot use it
        }

        // Try to fetch current console adapter
        try {
            $console = $sm->get('console');
            if (!$console instanceof ConsoleAdapter) {
                throw new ServiceNotFoundException();
            }
        } catch (ServiceNotFoundException $exception) {
            // The application does not have console adapter
            throw new RuntimeException('Cannot access Console adapter - is it defined in ServiceManager?');
        }

        // Retrieve the script's name (entry point)
        $scriptName = '';
        if ($request instanceof ConsoleRequest) {
            $scriptName = basename($request->getScriptName());
        }

        // Get application banner
        $banner = $this->getConsoleBanner($console, $mm);

        // Get application usage information
        $usage = $this->getConsoleUsage($console, $scriptName, $mm);

        // Inject the text into view
        $result  = $banner ? rtrim($banner, "\r\n")        : '';
        $result .= $usage  ? "\n\n" . trim($usage, "\r\n") : '';
        $result .= "\n"; // to ensure we output a final newline
        $result .= $this->reportNotFoundReason($e);
        $model->setResult($result);

        // Inject the result into MvcEvent
        $e->setResult($model);
    }

    /**
     * Build Console application banner text by querying currently loaded
     * modules.
     *
     * @param ModuleManagerInterface $moduleManager
     * @param ConsoleAdapter         $console
     * @return string
     */
    protected function getConsoleBanner(ConsoleAdapter $console, ModuleManagerInterface $moduleManager = null)
    {
        /*
         * Loop through all loaded modules and collect banners
         */
        $banners = array();
        if ($moduleManager !== null) {
            foreach ($moduleManager->getLoadedModules(false) as $module) {
                // Strict-type on ConsoleBannerProviderInterface, or duck-type
                // on the method it defines
                if (!$module instanceof ConsoleBannerProviderInterface
                    && !method_exists($module, 'getConsoleBanner')
                ) {
                    continue; // this module does not provide a banner
                }

                // Don't render empty completely empty lines
                $banner = $module->getConsoleBanner($console);
                if ($banner == '') {
                    continue;
                }

                // We colorize each banners in blue for visual emphasis
                $banners[] = $console->colorize($banner, ColorInterface::BLUE);
            }
        }

        /*
         * Handle an application with no defined banners
         */
        if (!count($banners)) {
            return sprintf("Zend Framework %s application\nUsage:\n", Version::VERSION);
        }

        /*
         * Join the banners by a newline character
         */
        return implode("\n", $banners);
    }

    /**
     * Build Console usage information by querying currently loaded modules.
     *
     * @param ConsoleAdapter         $console
     * @param string                 $scriptName
     * @param ModuleManagerInterface $moduleManager
     * @return string
     * @throws RuntimeException
     */
    protected function getConsoleUsage(
        ConsoleAdapter $console,
        $scriptName,
        ModuleManagerInterface $moduleManager = null
    ) {
        /*
         * Loop through all loaded modules and collect usage info
         */
        $usageInfo = array();

        if ($moduleManager !== null) {
            foreach ($moduleManager->getLoadedModules(false) as $name => $module) {
                // Strict-type on ConsoleUsageProviderInterface, or duck-type
                // on the method it defines
                if (!$module instanceof ConsoleUsageProviderInterface
                    && !method_exists($module, 'getConsoleUsage')
                ) {
                    continue; // this module does not provide usage info
                }

                // We prepend the usage by the module name (printed in red), so that each module is
                // clearly visible by the user
                $moduleName = sprintf("%s\n%s\n%s\n",
                    str_repeat('-', $console->getWidth()),
                    $name,
                    str_repeat('-', $console->getWidth())
                );

                $moduleName = $console->colorize($moduleName, ColorInterface::RED);

                $usage = $module->getConsoleUsage($console);

                // Normalize what we got from the module or discard
                if (is_array($usage) && !empty($usage)) {
                    array_unshift($usage, $moduleName);
                    $usageInfo[$name] = $usage;
                } elseif (is_string($usage) && ($usage != '')) {
                    $usageInfo[$name] = array($moduleName, $usage);
                }
            }
        }

        /*
         * Handle an application with no usage information
         */
        if (!count($usageInfo)) {
            // TODO: implement fetching available console routes from router
            return '';
        }

        /*
         * Transform arrays in usage info into columns, otherwise join everything together
         */
        $result    = '';
        $table     = false;
        $tableCols = 0;
        $tableType = 0;
        foreach ($usageInfo as $moduleName => $usage) {
            if (!is_string($usage) && !is_array($usage)) {
                throw new RuntimeException(sprintf(
                    'Cannot understand usage info for module "%s"',
                    $moduleName
                ));
            }

            if (is_string($usage)) {
                // It's a plain string - output as is
                $result .= $usage . "\n";
                continue;
            }

            // It's an array, analyze it
            foreach ($usage as $a => $b) {
                /*
                 * 'invocation method' => 'explanation'
                 */
                if (is_string($a) && is_string($b)) {
                    if (($tableCols !== 2 || $tableType != 1) && $table !== false) {
                        // render last table
                        $result .= $this->renderTable($table, $tableCols, $console->getWidth());
                        $table   = false;

                            // add extra newline for clarity
                        $result .= "\n";
                    }

                    // Colorize the command
                    $a = $console->colorize($scriptName . ' ' . $a, ColorInterface::GREEN);

                    $tableCols = 2;
                    $tableType = 1;
                    $table[]   = array($a, $b);
                    continue;
                }

                /*
                 * array('--param', '--explanation')
                 */
                if (is_array($b)) {
                    if ((count($b) != $tableCols || $tableType != 2) && $table !== false) {
                        // render last table
                        $result .= $this->renderTable($table, $tableCols, $console->getWidth());
                        $table   = false;

                        // add extra newline for clarity
                        $result .= "\n";
                    }

                    $tableCols = count($b);
                    $tableType = 2;
                    $table[]   = $b;
                    continue;
                }

                /*
                 * 'A single line of text'
                 */
                if ($table !== false) {
                    // render last table
                    $result .= $this->renderTable($table, $tableCols, $console->getWidth());
                    $table   = false;

                    // add extra newline for clarity
                    $result .= "\n";
                }

                $tableType = 0;
                $result   .= $b . "\n";
            }
        }

        // Finish last table
        if ($table !== false) {
            $result .= $this->renderTable($table, $tableCols, $console->getWidth());
        }

        return $result;
    }

    /**
     * Render a text table containing the data provided, that will fit inside console window's width.
     *
     * @param  $data
     * @param  $cols
     * @param  $consoleWidth
     * @return string
     */
    protected function renderTable($data, $cols, $consoleWidth)
    {
        $result  = '';
        $padding = 2;


        // If there is only 1 column, just concatenate it
        if ($cols == 1) {
            foreach ($data as $row) {
                if (! isset($row[0])) {
                    continue;
                }
                $result .= $row[0] . "\n";
            }
            return $result;
        }

        // Get the string wrapper supporting UTF-8 character encoding
        $strWrapper = StringUtils::getWrapper('UTF-8');

        // Determine max width for each column
        $maxW = array();
        for ($x = 1; $x <= $cols; $x += 1) {
            $maxW[$x] = 0;
            foreach ($data as $row) {
                $maxW[$x] = max($maxW[$x], $strWrapper->strlen($row[$x-1]) + $padding * 2);
            }
        }

        /*
         * Check if the sum of x-1 columns fit inside console window width - 10
         * chars. If columns do not fit inside console window, then we'll just
         * concatenate them and output as is.
         */
        $width = 0;
        for ($x = 1; $x < $cols; $x += 1) {
            $width += $maxW[$x];
        }

        if ($width >= $consoleWidth - 10) {
            foreach ($data as $row) {
                $result .= implode("    ", $row) . "\n";
            }
            return $result;
        }

        /*
         * Use Zend\Text\Table to render the table.
         * The last column will use the remaining space in console window
         * (minus 1 character to prevent double wrapping at the edge of the
         * screen).
         */
        $maxW[$cols] = $consoleWidth - $width -1;
        $table       = new Table\Table();
        $table->setColumnWidths($maxW);
        $table->setDecorator(new Table\Decorator\Blank());
        $table->setPadding(2);

        foreach ($data as $row) {
            $table->appendRow($row);
        }

        return $table->render();
    }

    /**
     * Report the 404 reason and/or exceptions
     *
     * @param  \Zend\EventManager\EventInterface $e
     * @return string
     */
    protected function reportNotFoundReason($e)
    {
        if (!$this->displayNotFoundReason()) {
            return '';
        }
        $exception = $e->getParam('exception', false);
        if (!$exception && !$this->reason) {
            return '';
        }

        $reason    = (isset($this->reason) && !empty($this->reason)) ? $this->reason : 'unknown';
        $reasons   = array(
            Application::ERROR_CONTROLLER_NOT_FOUND => 'Could not match to a controller',
            Application::ERROR_CONTROLLER_INVALID   => 'Invalid controller specified',
            Application::ERROR_ROUTER_NO_MATCH      => 'Invalid arguments or no arguments provided',
            'unknown'                               => 'Unknown',
        );
        $report = sprintf("\nReason for failure: %s\n", $reasons[$reason]);

        while ($exception instanceof \Exception) {
            $report   .= sprintf("Exception: %s\nTrace:\n%s\n", $exception->getMessage(), $exception->getTraceAsString());
            $exception = $exception->getPrevious();
        }
        return $report;
    }
}
