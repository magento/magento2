<?php

namespace Magento\Setup\Model\Installer\Event;

use Magento\Framework\Setup\SetupInterface;
use Magento\Setup\Exception;
use Zend\EventManager\EventInterface;
use Zend\EventManager\Exception\InvalidArgumentException;

/**
 * Provides base functionality for installer events.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
abstract class AbstractInstallerEvent implements EventInterface
{
    /** @var bool */
    private $isPropagationStopped = false;

    /** @var mixed */
    private $target;

    /** @var SetupInterface */
    private $setup;

    /**
     * @return SetupInterface
     */
    public function getSetup()
    {
        return $this->setup;
    }

    /**
     * @param SetupInterface $setup
     *
     * @return void
     */
    protected function setSetup(SetupInterface $setup)
    {
        $this->setup = $setup;
    }

    /**
     * Get a single parameter by name
     *
     * @param  string $name
     * @param  mixed $default Default value to return if parameter does not exist
     * @return mixed
     */
    public function getParam($name, $default = null)
    {
        $params = $this->getParams();
        return isset($params[$name]) ? $params[$name] : $default;
    }

    /**
     * @return array
     */
    abstract public function getParams();

    /**
     * Indicate whether or not the parent EventManagerInterface should stop propagating events. The only allowed method
     * that mutates internal state.
     *
     * @param bool $flag
     *
     * @return void
     */
    public function stopPropagation($flag = true)
    {
        $this->isPropagationStopped = (bool) $flag;
    }

    /**
     * Has this event indicated event propagation should stop?
     *
     * @return bool
     */
    public function propagationIsStopped()
    {
        return $this->isPropagationStopped;
    }

    /**
     * Get target/context from which event was triggered
     *
     * @return null|string|object
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Set the event target/context
     *
     * @param  null|string|object $target
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws Exception
     * @return void
     */
    public function setTarget($target)
    {
        throw new Exception("The internal state of this object is immutable, please don't use this method.");
    }

    /**
     * Set the event name
     *
     * @param  string $name
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws Exception
     * @return void
     */
    public function setName($name)
    {
        throw new Exception("The internal state of this object is immutable, please don't use this method.");
    }

    /**
     * Set event parameters
     *
     * @param  string $params
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws Exception
     * @return void
     */
    public function setParams($params)
    {
        throw new Exception("The internal state of this object is immutable, please don't use this method.");
    }

    /**
     * Set a single parameter by key
     *
     * @param  string $name
     * @param  mixed $value
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws Exception
     * @return void
     */
    public function setParam($name, $value)
    {
        throw new Exception("The internal state of this object is immutable, please don't use this method.");
    }

    /**
     * Use this to set the target on your constructor
     *
     * @param string|object $target
     *
     * @throws InvalidArgumentException
     * @return void
     */
    protected function internalSetTarget($target)
    {
        if (!\is_object($target) && !\is_string($target)) {
            throw new InvalidArgumentException('Parameter $target must be either a string or an object.');
        }

        $this->target = $target;
    }
}
