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
use Zend\Stdlib\ArrayUtils;
use Zend\Log\Exception;
use Zend\Log\Filter\FilterInterface;
use Zend\Log\Filter\Priority as PriorityFilter;
use Zend\Log\Formatter\FormatterInterface;
use Zend\Log\Logger;
use Zend\Log\WriterPluginManager;

/**
 * Buffers all events until the strategy determines to flush them.
 *
 * @see        http://packages.python.org/Logbook/api/handlers.html#logbook.FingersCrossedHandler
 */
class FingersCrossed extends AbstractWriter
{
    /**
     * The wrapped writer
     *
     * @var WriterInterface
     */
    protected $writer;

    /**
     * Writer plugins
     *
     * @var WriterPluginManager
     */
    protected $writerPlugins;

    /**
     * Flag if buffering is enabled
     *
     * @var bool
     */
    protected $buffering = true;

    /**
     * Oldest entries are removed from the buffer if bufferSize is reached.
     * 0 is infinte buffer size.
     *
     * @var int
     */
    protected $bufferSize;

    /**
     * array of log events
     *
     * @var array
     */
    protected $buffer = array();

    /**
     * Constructor
     *
     * @param WriterInterface|string|array|Traversable $writer Wrapped writer or array of configuration options
     * @param FilterInterface|int $filterOrPriority Filter or log priority which determines buffering of events
     * @param int $bufferSize Maximum buffer size
     */
    public function __construct($writer, $filterOrPriority = null, $bufferSize = 0)
    {
        $this->writer = $writer;

        if ($writer instanceof Traversable) {
            $writer = ArrayUtils::iteratorToArray($writer);
        }

        if (is_array($writer)) {
            $filterOrPriority = isset($writer['priority']) ? $writer['priority'] : null;
            $bufferSize       = isset($writer['bufferSize']) ? $writer['bufferSize'] : null;
            $writer           = isset($writer['writer']) ? $writer['writer'] : null;
        }

        if (null === $filterOrPriority) {
            $filterOrPriority = new PriorityFilter(Logger::WARN);
        } elseif (!$filterOrPriority instanceof FilterInterface) {
            $filterOrPriority = new PriorityFilter($filterOrPriority);
        }

        if (is_array($writer) && isset($writer['name'])) {
            $this->setWriter($writer['name'], $writer['options']);
        } else {
            $this->setWriter($writer);
        }
        $this->addFilter($filterOrPriority);
        $this->bufferSize = $bufferSize;
    }

    /**
     * Set a new writer
     *
     * @param  string|WriterInterface $writer
     * @param  array|null $options
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function setWriter($writer, array $options = null)
    {
        if (is_string($writer)) {
            $writer = $this->writerPlugin($writer, $options);
        }

        if (!$writer instanceof WriterInterface) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Writer must implement %s\WriterInterface; received "%s"',
                __NAMESPACE__,
                is_object($writer) ? get_class($writer) : gettype($writer)
            ));
        }

        $this->writer = $writer;
        return $this;
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
     * @return FingersCrossed
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
     * @return WriterInterface
     */
    public function writerPlugin($name, array $options = null)
    {
        return $this->getWriterPluginManager()->get($name, $options);
    }

    /**
     * Log a message to this writer.
     *
     * @param array $event log data event
     * @return void
     */
    public function write(array $event)
    {
        $this->doWrite($event);
    }

    /**
     * Check if buffered data should be flushed
     *
     * @param array $event event data
     * @return bool true if buffered data should be flushed
     */
    protected function isActivated(array $event)
    {
        foreach ($this->filters as $filter) {
            if (!$filter->filter($event)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Write message to buffer or delegate event data to the wrapped writer
     *
     * @param array $event event data
     * @return void
     */
    protected function doWrite(array $event)
    {
        if (!$this->buffering) {
            $this->writer->write($event);
            return;
        }

        $this->buffer[] = $event;

        if ($this->bufferSize > 0 && count($this->buffer) > $this->bufferSize) {
            array_shift($this->buffer);
        }

        if (!$this->isActivated($event)) {
            return;
        }

        $this->buffering = false;

        foreach ($this->buffer as $bufferedEvent) {
            $this->writer->write($bufferedEvent);
        }
    }

    /**
     * Resets the state of the handler.
     * Stops forwarding records to the wrapped writer
     */
    public function reset()
    {
        $this->buffering = true;
    }

    /**
     * Stub in accordance to parent method signature.
     * Fomatters must be set on the wrapped writer.
     *
     * @param string|FormatterInterface $formatter
     * @return WriterInterface
     */
    public function setFormatter($formatter)
    {
        return $this->writer;
    }

    /**
     * Record shutdown
     *
     * @return void
     */
    public function shutdown()
    {
        $this->writer->shutdown();
        $this->buffer = null;
    }
}
