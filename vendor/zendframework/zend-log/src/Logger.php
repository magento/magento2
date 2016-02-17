<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Log;

use DateTime;
use ErrorException;
use Traversable;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\SplPriorityQueue;

/**
 * Logging messages with a stack of backends
 */
class Logger implements LoggerInterface
{
    /**
     * @const int defined from the BSD Syslog message severities
     * @link http://tools.ietf.org/html/rfc3164
     */
    const EMERG  = 0;
    const ALERT  = 1;
    const CRIT   = 2;
    const ERR    = 3;
    const WARN   = 4;
    const NOTICE = 5;
    const INFO   = 6;
    const DEBUG  = 7;

    /**
     * Map native PHP errors to priority
     *
     * @var array
     */
    public static $errorPriorityMap = array(
        E_NOTICE            => self::NOTICE,
        E_USER_NOTICE       => self::NOTICE,
        E_WARNING           => self::WARN,
        E_CORE_WARNING      => self::WARN,
        E_USER_WARNING      => self::WARN,
        E_ERROR             => self::ERR,
        E_USER_ERROR        => self::ERR,
        E_CORE_ERROR        => self::ERR,
        E_RECOVERABLE_ERROR => self::ERR,
        E_PARSE             => self::ERR,
        E_COMPILE_ERROR     => self::ERR,
        E_COMPILE_WARNING   => self::ERR,
        E_STRICT            => self::DEBUG,
        E_DEPRECATED        => self::DEBUG,
        E_USER_DEPRECATED   => self::DEBUG,
    );

    /**
     * Registered error handler
     *
     * @var bool
     */
    protected static $registeredErrorHandler = false;

    /**
     * Registered shutdown error handler
     *
     * @var bool
     */
    protected static $registeredFatalErrorShutdownFunction = false;

    /**
     * Registered exception handler
     *
     * @var bool
     */
    protected static $registeredExceptionHandler = false;

    /**
     * List of priority code => priority (short) name
     *
     * @var array
     */
    protected $priorities = array(
        self::EMERG  => 'EMERG',
        self::ALERT  => 'ALERT',
        self::CRIT   => 'CRIT',
        self::ERR    => 'ERR',
        self::WARN   => 'WARN',
        self::NOTICE => 'NOTICE',
        self::INFO   => 'INFO',
        self::DEBUG  => 'DEBUG',
    );

    /**
     * Writers
     *
     * @var SplPriorityQueue
     */
    protected $writers;

    /**
     * Processors
     *
     * @var SplPriorityQueue
     */
    protected $processors;

    /**
     * Writer plugins
     *
     * @var WriterPluginManager
     */
    protected $writerPlugins;

    /**
     * Processor plugins
     *
     * @var ProcessorPluginManager
     */
    protected $processorPlugins;

    /**
     * Constructor
     *
     * Set options for a logger. Accepted options are:
     * - writers: array of writers to add to this logger
     * - exceptionhandler: if true register this logger as exceptionhandler
     * - errorhandler: if true register this logger as errorhandler
     *
     * @param  array|Traversable $options
     * @return Logger
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($options = null)
    {
        $this->writers    = new SplPriorityQueue();
        $this->processors = new SplPriorityQueue();

        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        if (!$options) {
            return;
        }

        if (!is_array($options)) {
            throw new Exception\InvalidArgumentException('Options must be an array or an object implementing \Traversable ');
        }

        // Inject writer plugin manager, if available
        if (isset($options['writer_plugin_manager'])
            && $options['writer_plugin_manager'] instanceof AbstractPluginManager
        ) {
            $this->setWriterPluginManager($options['writer_plugin_manager']);
        }

        // Inject processor plugin manager, if available
        if (isset($options['processor_plugin_manager'])
            && $options['processor_plugin_manager'] instanceof AbstractPluginManager
        ) {
            $this->setProcessorPluginManager($options['processor_plugin_manager']);
        }

        if (isset($options['writers']) && is_array($options['writers'])) {
            foreach ($options['writers'] as $writer) {
                if (!isset($writer['name'])) {
                    throw new Exception\InvalidArgumentException('Options must contain a name for the writer');
                }

                $priority      = (isset($writer['priority'])) ? $writer['priority'] : null;
                $writerOptions = (isset($writer['options'])) ? $writer['options'] : null;

                $this->addWriter($writer['name'], $priority, $writerOptions);
            }
        }

        if (isset($options['processors']) && is_array($options['processors'])) {
            foreach ($options['processors'] as $processor) {
                if (!isset($processor['name'])) {
                    throw new Exception\InvalidArgumentException('Options must contain a name for the processor');
                }

                $priority         = (isset($processor['priority'])) ? $processor['priority'] : null;
                $processorOptions = (isset($processor['options']))  ? $processor['options']  : null;

                $this->addProcessor($processor['name'], $priority, $processorOptions);
            }
        }

        if (isset($options['exceptionhandler']) && $options['exceptionhandler'] === true) {
            static::registerExceptionHandler($this);
        }

        if (isset($options['errorhandler']) && $options['errorhandler'] === true) {
            static::registerErrorHandler($this);
        }

        if (isset($options['fatal_error_shutdownfunction']) && $options['fatal_error_shutdownfunction'] === true) {
            static::registerFatalErrorShutdownFunction($this);
        }
    }

    /**
     * Shutdown all writers
     *
     * @return void
     */
    public function __destruct()
    {
        foreach ($this->writers as $writer) {
            try {
                $writer->shutdown();
            } catch (\Exception $e) {
            }
        }
    }

    /**
     * Get writer plugin manager
     *
     * @return WriterPluginManager
     */
    public function getWriterPluginManager()
    {
        if (null === $this->writerPlugins) {
            $this->setWriterPluginManager(new WriterPluginManager());
        }
        return $this->writerPlugins;
    }

    /**
     * Set writer plugin manager
     *
     * @param  string|WriterPluginManager $plugins
     * @return Logger
     * @throws Exception\InvalidArgumentException
     */
    public function setWriterPluginManager($plugins)
    {
        if (is_string($plugins)) {
            $plugins = new $plugins;
        }
        if (!$plugins instanceof WriterPluginManager) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Writer plugin manager must extend %s\WriterPluginManager; received %s',
                __NAMESPACE__,
                is_object($plugins) ? get_class($plugins) : gettype($plugins)
            ));
        }

        $this->writerPlugins = $plugins;
        return $this;
    }

    /**
     * Get writer instance
     *
     * @param string $name
     * @param array|null $options
     * @return Writer\WriterInterface
     */
    public function writerPlugin($name, array $options = null)
    {
        return $this->getWriterPluginManager()->get($name, $options);
    }

    /**
     * Add a writer to a logger
     *
     * @param  string|Writer\WriterInterface $writer
     * @param  int $priority
     * @param  array|null $options
     * @return Logger
     * @throws Exception\InvalidArgumentException
     */
    public function addWriter($writer, $priority = 1, array $options = null)
    {
        if (is_string($writer)) {
            $writer = $this->writerPlugin($writer, $options);
        } elseif (!$writer instanceof Writer\WriterInterface) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Writer must implement %s\Writer\WriterInterface; received "%s"',
                __NAMESPACE__,
                is_object($writer) ? get_class($writer) : gettype($writer)
            ));
        }
        $this->writers->insert($writer, $priority);

        return $this;
    }

    /**
     * Get writers
     *
     * @return SplPriorityQueue
     */
    public function getWriters()
    {
        return $this->writers;
    }

    /**
     * Set the writers
     *
     * @param  SplPriorityQueue $writers
     * @return Logger
     * @throws Exception\InvalidArgumentException
     */
    public function setWriters(SplPriorityQueue $writers)
    {
        foreach ($writers->toArray() as $writer) {
            if (!$writer instanceof Writer\WriterInterface) {
                throw new Exception\InvalidArgumentException('Writers must be a SplPriorityQueue of Zend\Log\Writer');
            }
        }
        $this->writers = $writers;
        return $this;
    }

    /**
     * Get processor plugin manager
     *
     * @return ProcessorPluginManager
     */
    public function getProcessorPluginManager()
    {
        if (null === $this->processorPlugins) {
            $this->setProcessorPluginManager(new ProcessorPluginManager());
        }
        return $this->processorPlugins;
    }

    /**
     * Set processor plugin manager
     *
     * @param  string|ProcessorPluginManager $plugins
     * @return Logger
     * @throws Exception\InvalidArgumentException
     */
    public function setProcessorPluginManager($plugins)
    {
        if (is_string($plugins)) {
            $plugins = new $plugins;
        }
        if (!$plugins instanceof ProcessorPluginManager) {
            throw new Exception\InvalidArgumentException(sprintf(
                'processor plugin manager must extend %s\ProcessorPluginManager; received %s',
                __NAMESPACE__,
                is_object($plugins) ? get_class($plugins) : gettype($plugins)
            ));
        }

        $this->processorPlugins = $plugins;
        return $this;
    }

    /**
     * Get processor instance
     *
     * @param string $name
     * @param array|null $options
     * @return Processor\ProcessorInterface
     */
    public function processorPlugin($name, array $options = null)
    {
        return $this->getProcessorPluginManager()->get($name, $options);
    }

    /**
     * Add a processor to a logger
     *
     * @param  string|Processor\ProcessorInterface $processor
     * @param  int $priority
     * @param  array|null $options
     * @return Logger
     * @throws Exception\InvalidArgumentException
     */
    public function addProcessor($processor, $priority = 1, array $options = null)
    {
        if (is_string($processor)) {
            $processor = $this->processorPlugin($processor, $options);
        } elseif (!$processor instanceof Processor\ProcessorInterface) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Processor must implement Zend\Log\ProcessorInterface; received "%s"',
                is_object($processor) ? get_class($processor) : gettype($processor)
            ));
        }
        $this->processors->insert($processor, $priority);

        return $this;
    }

    /**
     * Get processors
     *
     * @return SplPriorityQueue
     */
    public function getProcessors()
    {
        return $this->processors;
    }

    /**
     * Add a message as a log entry
     *
     * @param  int $priority
     * @param  mixed $message
     * @param  array|Traversable $extra
     * @return Logger
     * @throws Exception\InvalidArgumentException if message can't be cast to string
     * @throws Exception\InvalidArgumentException if extra can't be iterated over
     * @throws Exception\RuntimeException if no log writer specified
     */
    public function log($priority, $message, $extra = array())
    {
        if (!is_int($priority) || ($priority<0) || ($priority>=count($this->priorities))) {
            throw new Exception\InvalidArgumentException(sprintf(
                '$priority must be an integer >= 0 and < %d; received %s',
                count($this->priorities),
                var_export($priority, 1)
            ));
        }
        if (is_object($message) && !method_exists($message, '__toString')) {
            throw new Exception\InvalidArgumentException(
                '$message must implement magic __toString() method'
            );
        }

        if (!is_array($extra) && !$extra instanceof Traversable) {
            throw new Exception\InvalidArgumentException(
                '$extra must be an array or implement Traversable'
            );
        } elseif ($extra instanceof Traversable) {
            $extra = ArrayUtils::iteratorToArray($extra);
        }

        if ($this->writers->count() === 0) {
            throw new Exception\RuntimeException('No log writer specified');
        }

        $timestamp = new DateTime();

        if (is_array($message)) {
            $message = var_export($message, true);
        }

        $event = array(
            'timestamp'    => $timestamp,
            'priority'     => (int) $priority,
            'priorityName' => $this->priorities[$priority],
            'message'      => (string) $message,
            'extra'        => $extra,
        );

        foreach ($this->processors->toArray() as $processor) {
            $event = $processor->process($event);
        }

        foreach ($this->writers->toArray() as $writer) {
            $writer->write($event);
        }

        return $this;
    }

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return Logger
     */
    public function emerg($message, $extra = array())
    {
        return $this->log(self::EMERG, $message, $extra);
    }

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return Logger
     */
    public function alert($message, $extra = array())
    {
        return $this->log(self::ALERT, $message, $extra);
    }

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return Logger
     */
    public function crit($message, $extra = array())
    {
        return $this->log(self::CRIT, $message, $extra);
    }

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return Logger
     */
    public function err($message, $extra = array())
    {
        return $this->log(self::ERR, $message, $extra);
    }

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return Logger
     */
    public function warn($message, $extra = array())
    {
        return $this->log(self::WARN, $message, $extra);
    }

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return Logger
     */
    public function notice($message, $extra = array())
    {
        return $this->log(self::NOTICE, $message, $extra);
    }

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return Logger
     */
    public function info($message, $extra = array())
    {
        return $this->log(self::INFO, $message, $extra);
    }

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return Logger
     */
    public function debug($message, $extra = array())
    {
        return $this->log(self::DEBUG, $message, $extra);
    }

    /**
     * Register logging system as an error handler to log PHP errors
     *
     * @link http://www.php.net/manual/function.set-error-handler.php
     * @param  Logger $logger
     * @param  bool   $continueNativeHandler
     * @return mixed  Returns result of set_error_handler
     * @throws Exception\InvalidArgumentException if logger is null
     */
    public static function registerErrorHandler(Logger $logger, $continueNativeHandler = false)
    {
        // Only register once per instance
        if (static::$registeredErrorHandler) {
            return false;
        }

        $errorPriorityMap = static::$errorPriorityMap;

        $previous = set_error_handler(function ($level, $message, $file, $line) use ($logger, $errorPriorityMap, $continueNativeHandler) {
            $iniLevel = error_reporting();

            if ($iniLevel & $level) {
                if (isset($errorPriorityMap[$level])) {
                    $priority = $errorPriorityMap[$level];
                } else {
                    $priority = Logger::INFO;
                }
                $logger->log($priority, $message, array(
                    'errno'   => $level,
                    'file'    => $file,
                    'line'    => $line,
                ));
            }

            return !$continueNativeHandler;
        });

        static::$registeredErrorHandler = true;
        return $previous;
    }

    /**
     * Unregister error handler
     *
     */
    public static function unregisterErrorHandler()
    {
        restore_error_handler();
        static::$registeredErrorHandler = false;
    }

    /**
     * Register a shutdown handler to log fatal errors
     *
     * @link http://www.php.net/manual/function.register-shutdown-function.php
     * @param  Logger $logger
     * @return bool
     */
    public static function registerFatalErrorShutdownFunction(Logger $logger)
    {
        // Only register once per instance
        if (static::$registeredFatalErrorShutdownFunction) {
            return false;
        }

        $errorPriorityMap = static::$errorPriorityMap;

        register_shutdown_function(function () use ($logger, $errorPriorityMap) {
            $error = error_get_last();

            if (null === $error
                || ! in_array(
                    $error['type'],
                    array(
                        E_ERROR,
                        E_PARSE,
                        E_CORE_ERROR,
                        E_CORE_WARNING,
                        E_COMPILE_ERROR,
                        E_COMPILE_WARNING
                    ),
                    true
                )
            ) {
                return;
            }

            $logger->log($errorPriorityMap[$error['type']],
                $error['message'],
                array(
                    'file' => $error['file'],
                    'line' => $error['line'],
                )
            );
        });

        static::$registeredFatalErrorShutdownFunction = true;

        return true;
    }

    /**
     * Register logging system as an exception handler to log PHP exceptions
     *
     * @link http://www.php.net/manual/en/function.set-exception-handler.php
     * @param Logger $logger
     * @return bool
     * @throws Exception\InvalidArgumentException if logger is null
     */
    public static function registerExceptionHandler(Logger $logger)
    {
        // Only register once per instance
        if (static::$registeredExceptionHandler) {
            return false;
        }

        if ($logger === null) {
            throw new Exception\InvalidArgumentException('Invalid Logger specified');
        }

        $errorPriorityMap = static::$errorPriorityMap;

        set_exception_handler(function ($exception) use ($logger, $errorPriorityMap) {
            $logMessages = array();

            do {
                $priority = Logger::ERR;
                if ($exception instanceof ErrorException && isset($errorPriorityMap[$exception->getSeverity()])) {
                    $priority = $errorPriorityMap[$exception->getSeverity()];
                }

                $extra = array(
                    'file'  => $exception->getFile(),
                    'line'  => $exception->getLine(),
                    'trace' => $exception->getTrace(),
                );
                if (isset($exception->xdebug_message)) {
                    $extra['xdebug'] = $exception->xdebug_message;
                }

                $logMessages[] = array(
                    'priority' => $priority,
                    'message'  => $exception->getMessage(),
                    'extra'    => $extra,
                );
                $exception = $exception->getPrevious();
            } while ($exception);

            foreach (array_reverse($logMessages) as $logMessage) {
                $logger->log($logMessage['priority'], $logMessage['message'], $logMessage['extra']);
            }
        });

        static::$registeredExceptionHandler = true;
        return true;
    }

    /**
     * Unregister exception handler
     */
    public static function unregisterExceptionHandler()
    {
        restore_exception_handler();
        static::$registeredExceptionHandler = false;
    }
}
