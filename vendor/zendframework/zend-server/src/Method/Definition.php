<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Server\Method;

use Zend\Server;

/**
 * Method definition metadata
 */
class Definition
{
    /**
     * @var \Zend\Server\Method\Callback
     */
    protected $callback;

    /**
     * @var array
     */
    protected $invokeArguments = array();

    /**
     * @var string
     */
    protected $methodHelp = '';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var null|object
     */
    protected $object;

    /**
     * @var array Array of \Zend\Server\Method\Prototype objects
     */
    protected $prototypes = array();

    /**
     * Constructor
     *
     * @param  null|array $options
     */
    public function __construct($options = null)
    {
        if ((null !== $options) && is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * Set object state from options
     *
     * @param  array $options
     * @return \Zend\Server\Method\Definition
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    /**
     * Set method name
     *
     * @param  string $name
     * @return \Zend\Server\Method\Definition
     */
    public function setName($name)
    {
        $this->name = (string) $name;
        return $this;
    }

    /**
     * Get method name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set method callback
     *
     * @param  array|\Zend\Server\Method\Callback $callback
     * @throws Server\Exception\InvalidArgumentException
     * @return \Zend\Server\Method\Definition
     */
    public function setCallback($callback)
    {
        if (is_array($callback)) {
            $callback = new Callback($callback);
        } elseif (!$callback instanceof Callback) {
            throw new Server\Exception\InvalidArgumentException('Invalid method callback provided');
        }
        $this->callback = $callback;
        return $this;
    }

    /**
     * Get method callback
     *
     * @return \Zend\Server\Method\Callback
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * Add prototype to method definition
     *
     * @param  array|\Zend\Server\Method\Prototype $prototype
     * @throws Server\Exception\InvalidArgumentException
     * @return \Zend\Server\Method\Definition
     */
    public function addPrototype($prototype)
    {
        if (is_array($prototype)) {
            $prototype = new Prototype($prototype);
        } elseif (!$prototype instanceof Prototype) {
            throw new Server\Exception\InvalidArgumentException('Invalid method prototype provided');
        }
        $this->prototypes[] = $prototype;
        return $this;
    }

    /**
     * Add multiple prototypes at once
     *
     * @param  array $prototypes Array of \Zend\Server\Method\Prototype objects or arrays
     * @return \Zend\Server\Method\Definition
     */
    public function addPrototypes(array $prototypes)
    {
        foreach ($prototypes as $prototype) {
            $this->addPrototype($prototype);
        }
        return $this;
    }

    /**
     * Set all prototypes at once (overwrites)
     *
     * @param  array $prototypes Array of \Zend\Server\Method\Prototype objects or arrays
     * @return \Zend\Server\Method\Definition
     */
    public function setPrototypes(array $prototypes)
    {
        $this->prototypes = array();
        $this->addPrototypes($prototypes);
        return $this;
    }

    /**
     * Get all prototypes
     *
     * @return array $prototypes Array of \Zend\Server\Method\Prototype objects or arrays
     */
    public function getPrototypes()
    {
        return $this->prototypes;
    }

    /**
     * Set method help
     *
     * @param  string $methodHelp
     * @return \Zend\Server\Method\Definition
     */
    public function setMethodHelp($methodHelp)
    {
        $this->methodHelp = (string) $methodHelp;
        return $this;
    }

    /**
     * Get method help
     *
     * @return string
     */
    public function getMethodHelp()
    {
        return $this->methodHelp;
    }

    /**
     * Set object to use with method calls
     *
     * @param  object $object
     * @throws Server\Exception\InvalidArgumentException
     * @return \Zend\Server\Method\Definition
     */
    public function setObject($object)
    {
        if (!is_object($object) && (null !== $object)) {
            throw new Server\Exception\InvalidArgumentException('Invalid object passed to ' . __CLASS__ . '::' . __METHOD__);
        }
        $this->object = $object;
        return $this;
    }

    /**
     * Get object to use with method calls
     *
     * @return null|object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Set invoke arguments
     *
     * @param  array $invokeArguments
     * @return \Zend\Server\Method\Definition
     */
    public function setInvokeArguments(array $invokeArguments)
    {
        $this->invokeArguments = $invokeArguments;
        return $this;
    }

    /**
     * Retrieve invoke arguments
     *
     * @return array
     */
    public function getInvokeArguments()
    {
        return $this->invokeArguments;
    }

    /**
     * Serialize to array
     *
     * @return array
     */
    public function toArray()
    {
        $prototypes = $this->getPrototypes();
        $signatures = array();
        foreach ($prototypes as $prototype) {
            $signatures[] = $prototype->toArray();
        }

        return array(
            'name'            => $this->getName(),
            'callback'        => $this->getCallback()->toArray(),
            'prototypes'      => $signatures,
            'methodHelp'      => $this->getMethodHelp(),
            'invokeArguments' => $this->getInvokeArguments(),
            'object'          => $this->getObject(),
        );
    }
}
