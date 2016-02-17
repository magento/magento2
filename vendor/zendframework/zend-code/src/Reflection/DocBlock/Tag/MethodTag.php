<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Reflection\DocBlock\Tag;

class MethodTag implements TagInterface, PhpDocTypedTagInterface
{
    /**
     * Return value type
     *
     * @var array
     */
    protected $types = array();

    /**
     * @var string
     */
    protected $methodName = null;

    /**
     * @var string
     */
    protected $description = null;

    /**
     * Is static method
     *
     * @var bool
     */
    protected $isStatic = false;

    /**
     * @return string
     */
    public function getName()
    {
        return 'method';
    }

    /**
     * Initializer
     *
     * @param  string $tagDocblockLine
     */
    public function initialize($tagDocblockLine)
    {
        $match = array();

        if (!preg_match('#^(static[\s]+)?(.+[\s]+)?(.+\(\))[\s]*(.*)$#m', $tagDocblockLine, $match)) {
            return;
        }

        if ($match[1] !== '') {
            $this->isStatic = true;
        }

        if ($match[2] !== '') {
            $this->types = explode('|', rtrim($match[2]));
        }

        $this->methodName = $match[3];

        if ($match[4] !== '') {
            $this->description = $match[4];
        }
    }

    /**
     * Get return value type
     *
     * @return null|string
     * @deprecated 2.0.4 use getTypes instead
     */
    public function getReturnType()
    {
        if (empty($this->types)) {
            return;
        }

        return $this->types[0];
    }

    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @return string
     */
    public function getMethodName()
    {
        return $this->methodName;
    }

    /**
     * @return null|string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return bool
     */
    public function isStatic()
    {
        return $this->isStatic;
    }

    public function __toString()
    {
        return 'DocBlock Tag [ * @' . $this->getName() . ' ]' . PHP_EOL;
    }
}
