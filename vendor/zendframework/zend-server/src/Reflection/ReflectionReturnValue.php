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
 * Return value reflection
 *
 * Stores the return value type and description
 */
class ReflectionReturnValue
{
    /**
     * Return value type
     * @var string
     */
    protected $type;

    /**
     * Return value description
     * @var string
     */
    protected $description;

    /**
     * Constructor
     *
     * @param string $type Return value type
     * @param string $description Return value type
     */
    public function __construct($type = 'mixed', $description = '')
    {
        $this->setType($type);
        $this->setDescription($description);
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
}
