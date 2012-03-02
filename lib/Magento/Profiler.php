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
 * @category    Magento
 * @package     Magento_Profiler
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Static class that represents profiling tool
 */
class Magento_Profiler
{
    /**
     * Separator literal to assemble timer identifier from timer names
     */
    const NESTING_SEPARATOR = '->';

    /**
     * FETCH_* constants represent keys to retrieve profiling results
     */
    const FETCH_TIME    = 'sum';
    const FETCH_COUNT   = 'count';
    const FETCH_AVG     = 'avg';
    const FETCH_REALMEM = 'realmem';
    const FETCH_EMALLOC = 'emalloc';

    /**
     * Storage for timers statistics
     *
     * @var array
     */
    static private $_timers = array();

    /**
     * Whether profiling is active or not
     *
     * @var bool
     */
    static private $_enabled = false;

    /**
     * Nesting path that represents namespace to resolve timer names
     *
     * @var array
     */
    static private $_currentPath = array();

    /**
     * Collection of profiler outputs
     *
     * @var array
     */
    static private $_outputs = array();

    /**
     * Whether an initialization is done or not
     *
     * @var bool
     */
    static protected $_isInitialized = false;

    /**
     * Supported timer statistics keys
     *
     * @var array
     */
    private static $_supportedFetchKeys = array(
        self::FETCH_TIME,
        self::FETCH_AVG,
        self::FETCH_COUNT,
        self::FETCH_EMALLOC,
        self::FETCH_REALMEM,
    );

    /**
     * Retrieve unique identifier among all timers
     *
     * @param string|null $timerName Timer name
     * @return string
     */
    private static function _getTimerId($timerName = null)
    {
        $currentPath = self::$_currentPath;
        if ($timerName) {
            $currentPath[] = $timerName;
        }
        return implode(self::NESTING_SEPARATOR, $currentPath);
    }

    /**
     * Initialize the profiler before the first enabling
     */
    protected static function _initialize()
    {
        register_shutdown_function(array(__CLASS__, 'display'));
    }

    /**
     * Enable profiling.
     * Any call to profiler does nothing until profiler is enabled.
     */
    public static function enable()
    {
        if (!self::$_isInitialized) {
            static::_initialize();
            self::$_isInitialized = true;
        }
        self::$_enabled = true;
    }

    /**
     * Disable profiling.
     * Any call to profiler is silently ignored while profiler is disabled.
     */
    public static function disable()
    {
        self::$_enabled = false;
    }

    /**
     * Reset collected statistics for specified timer or for whole profiler if timer name is omitted
     *
     * @param string|null $timerId
     */
    public static function reset($timerId = null)
    {
        if ($timerId === null) {
            self::$_timers = array();
            self::$_currentPath = array();
            return;
        }
        self::$_timers[$timerId] = array(
            'start'             => false,
            self::FETCH_TIME    => 0,
            self::FETCH_COUNT   => 0,
            self::FETCH_REALMEM => 0,
            self::FETCH_EMALLOC => 0,
        );
    }

    /**
     * Start collecting statistics for specified timer
     *
     * @param string $timerName
     * @throws Varien_Exception
     */
    public static function start($timerName)
    {
        if (!self::$_enabled) {
            return;
        }

        if (strpos($timerName, self::NESTING_SEPARATOR) !== false) {
            throw new Varien_Exception('Timer name must not contain a nesting separator.');
        }

        $timerId = self::_getTimerId($timerName);

        /*
         * Timer can be already initialized, for example:
         * self::start('timer'); // <- initialization
         * self::stop('timer');
         * self::start('timer'); // <- already initialized
         * self::stop('timer');
         */
        if (empty(self::$_timers[$timerId])) {
            self::reset($timerId);
        }

        /* Continue collecting timers statistics under the latest started one */
        self::$_currentPath[] = $timerName;

        self::$_timers[$timerId]['realmem_start'] = memory_get_usage(true);
        self::$_timers[$timerId]['emalloc_start'] = memory_get_usage();
        self::$_timers[$timerId]['start'] = microtime(true);
        self::$_timers[$timerId][self::FETCH_COUNT]++;
    }

    /**
     * Stop recording statistics for specified timer.
     * Call with no arguments to stop the recently started timer.
     * Only the latest started timer can be stopped.
     *
     * @param  string|null $timerName
     * @throws Varien_Exception
     */
    public static function stop($timerName = null)
    {
        if (!self::$_enabled) {
            return;
        }

        /* Get current time as quick as possible to make more accurate calculations */
        $time = microtime(true);

        $latestTimerName = end(self::$_currentPath);
        if ($timerName !== null && $timerName !== $latestTimerName) {
            if (in_array($timerName, self::$_currentPath)) {
                $exceptionMsg = sprintf('Timer "%s" should be stopped before "%s".', $latestTimerName, $timerName);
            } else {
                $exceptionMsg = sprintf('Timer "%s" has not been started.', $timerName);
            }
            throw new Varien_Exception($exceptionMsg);
        }

        $timerId = self::_getTimerId();

        self::$_timers[$timerId][self::FETCH_TIME] += ($time - self::$_timers[$timerId]['start']);
        self::$_timers[$timerId]['start'] = false;
        self::$_timers[$timerId][self::FETCH_REALMEM] += memory_get_usage(true);
        self::$_timers[$timerId][self::FETCH_REALMEM] -= self::$_timers[$timerId]['realmem_start'];
        self::$_timers[$timerId][self::FETCH_EMALLOC] += memory_get_usage();
        self::$_timers[$timerId][self::FETCH_EMALLOC] -= self::$_timers[$timerId]['emalloc_start'];

        /* Move one level up in timers nesting tree */
        array_pop(self::$_currentPath);
    }

    /**
     * Retrieve statistics on specified timer
     *
     * @param  string $timerId
     * @param  string $key Information to return
     * @return int|float
     * @throws Varien_Exception
     */
    public static function fetch($timerId, $key = self::FETCH_TIME)
    {
        if (empty(self::$_timers[$timerId])) {
            throw new Varien_Exception(sprintf('Timer "%s" does not exist.', $timerId));
        }
        if (!in_array($key, self::$_supportedFetchKeys)) {
            throw new Varien_Exception(sprintf('Requested key "%s" is not supported.', $key));
        }
        /* FETCH_AVG = FETCH_TIME / FETCH_COUNT */
        $isAvg = ($key == self::FETCH_AVG);
        if ($isAvg) {
            $key = self::FETCH_TIME;
        }
        $result = self::$_timers[$timerId][$key];
        if ($key == self::FETCH_TIME && self::$_timers[$timerId]['start'] !== false) {
            $result += (microtime(true) - self::$_timers[$timerId]['start']);
        }
        if ($isAvg) {
            $count = self::$_timers[$timerId][self::FETCH_COUNT];
            $result = ($count ? $result / $count : 0);
        }
        return $result;
    }

    /**
     * Retrieve the list of unique timer identifiers
     *
     * @return array
     */
    public static function getTimers()
    {
        return array_keys(self::$_timers);
    }

    /**
     * Register profiler output instance to display profiling result at the end of execution
     *
     * @param Magento_Profiler_OutputAbstract $output
     */
    public static function registerOutput(Magento_Profiler_OutputAbstract $output)
    {
        self::enable();
        self::$_outputs[] = $output;
    }

    /**
     * Display collected statistics with registered outputs
     */
    public static function display()
    {
        if (!self::$_enabled) {
            return;
        }
        /** @var $output Magento_Profiler_OutputAbstract */
        foreach (self::$_outputs as $output) {
            $output->display();
        }
    }
}
