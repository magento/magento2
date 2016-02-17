<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Log\Writer;

use Traversable;
use Zend\Log\Exception;
use Zend\Log\Filter;
use Zend\Log\Formatter;
use Zend\Stdlib\ErrorHandler;

abstract class AbstractWriter implements WriterInterface
{
    /**
     * Filter plugins
     *
     * @var FilterPluginManager
     */
    protected $filterPlugins;

    /**
     * Formatter plugins
     *
     * @var FormatterPluginManager
     */
    protected $formatterPlugins;

    /**
     * Filter chain
     *
     * @var Filter\FilterInterface[]
     */
    protected $filters = array();

    /**
     * Formats the log message before writing
     *
     * @var Formatter\FormatterInterface
     */
    protected $formatter;

    /**
     * Use Zend\Stdlib\ErrorHandler to report errors during calls to write
     *
     * @var bool
     */
    protected $convertWriteErrorsToExceptions = true;

    /**
     * Error level passed to Zend\Stdlib\ErrorHandler::start for errors reported during calls to write
     *
     * @var bool
     */
    protected $errorsToExceptionsConversionLevel = E_WARNING;

    /**
     * Constructor
     *
     * Set options for a writer. Accepted options are:
     * - filters: array of filters to add to this filter
     * - formatter: formatter for this writer
     *
     * @param  array|Traversable $options
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($options = null)
    {
        if ($options instanceof Traversable) {
            $options = iterator_to_array($options);
        }

        if (is_array($options)) {
            if (isset($options['filters'])) {
                $filters = $options['filters'];
                if (is_int($filters) || is_string($filters) || $filters instanceof Filter\FilterInterface) {
                    $this->addFilter($filters);
                } elseif (is_array($filters)) {
                    foreach ($filters as $filter) {
                        if (is_int($filter) || is_string($filter) || $filter instanceof Filter\FilterInterface) {
                            $this->addFilter($filter);
                        } elseif (is_array($filter)) {
                            if (!isset($filter['name'])) {
                                throw new Exception\InvalidArgumentException('Options must contain a name for the filter');
                            }
                            $filterOptions = (isset($filter['options'])) ? $filter['options'] : null;
                            $this->addFilter($filter['name'], $filterOptions);
                        }
                    }
                }
            }

            if (isset($options['formatter'])) {
                $formatter = $options['formatter'];
                if (is_string($formatter) || $formatter instanceof Formatter\FormatterInterface) {
                    $this->setFormatter($formatter);
                } elseif (is_array($formatter)) {
                    if (!isset($formatter['name'])) {
                        throw new Exception\InvalidArgumentException('Options must contain a name for the formatter');
                    }
                    $formatterOptions = (isset($formatter['options'])) ? $formatter['options'] : null;
                    $this->setFormatter($formatter['name'], $formatterOptions);
                }
            }
        }
    }

    /**
     * Add a filter specific to this writer.
     *
     * @param  int|string|Filter\FilterInterface $filter
     * @param  array|null $options
     * @return AbstractWriter
     * @throws Exception\InvalidArgumentException
     */
    public function addFilter($filter, array $options = null)
    {
        if (is_int($filter)) {
            $filter = new Filter\Priority($filter);
        }

        if (is_string($filter)) {
            $filter = $this->filterPlugin($filter, $options);
        }

        if (!$filter instanceof Filter\FilterInterface) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Filter must implement %s\Filter\FilterInterface; received "%s"',
                __NAMESPACE__,
                is_object($filter) ? get_class($filter) : gettype($filter)
            ));
        }

        $this->filters[] = $filter;
        return $this;
    }

    /**
     * Get filter plugin manager
     *
     * @return FilterPluginManager
     */
    public function getFilterPluginManager()
    {
        if (null === $this->filterPlugins) {
            $this->setFilterPluginManager(new FilterPluginManager());
        }
        return $this->filterPlugins;
    }

    /**
     * Set filter plugin manager
     *
     * @param  string|FilterPluginManager $plugins
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function setFilterPluginManager($plugins)
    {
        if (is_string($plugins)) {
            $plugins = new $plugins;
        }
        if (!$plugins instanceof FilterPluginManager) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Writer plugin manager must extend %s\FilterPluginManager; received %s',
                __NAMESPACE__,
                is_object($plugins) ? get_class($plugins) : gettype($plugins)
            ));
        }

        $this->filterPlugins = $plugins;
        return $this;
    }

    /**
     * Get filter instance
     *
     * @param string $name
     * @param array|null $options
     * @return Filter\FilterInterface
     */
    public function filterPlugin($name, array $options = null)
    {
        return $this->getFilterPluginManager()->get($name, $options);
    }

    /**
     * Get formatter plugin manager
     *
     * @return FormatterPluginManager
     */
    public function getFormatterPluginManager()
    {
        if (null === $this->formatterPlugins) {
            $this->setFormatterPluginManager(new FormatterPluginManager());
        }
        return $this->formatterPlugins;
    }

    /**
     * Set formatter plugin manager
     *
     * @param  string|FormatterPluginManager $plugins
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function setFormatterPluginManager($plugins)
    {
        if (is_string($plugins)) {
            $plugins = new $plugins;
        }
        if (!$plugins instanceof FormatterPluginManager) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'Writer plugin manager must extend %s\FormatterPluginManager; received %s',
                    __NAMESPACE__,
                    is_object($plugins) ? get_class($plugins) : gettype($plugins)
                )
            );
        }

        $this->formatterPlugins = $plugins;
        return $this;
    }

    /**
     * Get formatter instance
     *
     * @param string $name
     * @param array|null $options
     * @return Formatter\FormatterInterface
     */
    public function formatterPlugin($name, array $options = null)
    {
        return $this->getFormatterPluginManager()->get($name, $options);
    }

    /**
     * Log a message to this writer.
     *
     * @param array $event log data event
     * @return void
     */
    public function write(array $event)
    {
        foreach ($this->filters as $filter) {
            if (!$filter->filter($event)) {
                return;
            }
        }

        $errorHandlerStarted = false;

        if ($this->convertWriteErrorsToExceptions && !ErrorHandler::started()) {
            ErrorHandler::start($this->errorsToExceptionsConversionLevel);
            $errorHandlerStarted = true;
        }

        try {
            $this->doWrite($event);
        } catch (\Exception $e) {
            if ($errorHandlerStarted) {
                ErrorHandler::stop();
            }
            throw $e;
        }

        if ($errorHandlerStarted) {
            $error = ErrorHandler::stop();
            if ($error) {
                throw new Exception\RuntimeException("Unable to write", 0, $error);
            }
        }
    }

    /**
     * Set a new formatter for this writer
     *
     * @param  string|Formatter\FormatterInterface $formatter
     * @param  array|null $options
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function setFormatter($formatter, array $options = null)
    {
        if (is_string($formatter)) {
            $formatter = $this->formatterPlugin($formatter, $options);
        }

        if (!$formatter instanceof Formatter\FormatterInterface) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Formatter must implement %s\Formatter\FormatterInterface; received "%s"',
                __NAMESPACE__,
                is_object($formatter) ? get_class($formatter) : gettype($formatter)
            ));
        }

        $this->formatter = $formatter;
        return $this;
    }

    /**
     * Get formatter
     *
     * @return Formatter\FormatterInterface
     */
    protected function getFormatter()
    {
        return $this->formatter;
    }

    /**
     * Check if the writer has a formatter
     *
     * @return bool
     */
    protected function hasFormatter()
    {
        return $this->formatter instanceof Formatter\FormatterInterface;
    }

    /**
     * Set convert write errors to exception flag
     *
     * @param bool $convertErrors
     */
    public function setConvertWriteErrorsToExceptions($convertErrors)
    {
        $this->convertWriteErrorsToExceptions = $convertErrors;
    }

    /**
     * Perform shutdown activities such as closing open resources
     *
     * @return void
     */
    public function shutdown()
    {
    }

    /**
     * Write a message to the log
     *
     * @param array $event log data event
     * @return void
     */
    abstract protected function doWrite(array $event);
}
