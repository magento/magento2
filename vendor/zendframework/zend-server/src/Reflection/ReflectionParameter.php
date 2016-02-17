<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Server\Reflection;

/**
 * Parameter Reflection
 *
 * Decorates a ReflectionParameter to allow setting the parameter type
 */
class ReflectionParameter
{
    /**
     * @var \ReflectionParameter
     */
    protected $reflection;

    /**
     * Parameter position
     * @var int
     */
    protected $position;

    /**
     * Parameter type
     * @var string
     */
    protected $type;

    /**
     * Parameter description
     * @var string
     */
    protected $description;

    /**
     * Constructor
     *
     * @param \ReflectionParameter $r
     * @param string $type Parameter type
     * @param string $description Parameter description
     */
    public function __construct(\ReflectionParameter $r, $type = 'mixed', $description = '')
    {
        $this->reflection = $r;
        $this->setType($type);
        $this->setDescription($description);
    }

    /**
     * Proxy reflection calls
     *
     * @param string $method
     * @param array $args
     * @throws Exception\BadMethodCallException
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (method_exists($this->reflection, $method)) {
            return call_user_func_array(array($this->reflection, $method), $args);
        }

        throw new Exception\BadMethodCallException('Invalid reflection method');
    }

    /**
     * Retrieve parameter type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set parameter type
     *
     * @param string|null $type
     * @throws Exception\InvalidArgumentException
     * @return void
     */
    public function setType($type)
    {
        if (!is_string($type) && (null !== $type)) {
            throw new Exception\InvalidArgumentException('Invalid parameter type');
        }

        $this->type = $type;
    }

    /**
     * Retrieve parameter description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set parameter description
     *
     * @param string|null $description
     * @throws Exception\InvalidArgumentException
     * @return void
     */
    public function setDescription($description)
    {
        if (!is_string($description) && (null !== $description)) {
            throw new Exception\InvalidArgumentException('Invalid parameter description');
        }

        $this->description = $description;
    }

    /**
     * Set parameter position
     *
     * @param int $index
     * @return void
     */
    public function setPosition($index)
    {
        $this->position = (int) $index;
    }

    /**
     * Return parameter position
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }
}
