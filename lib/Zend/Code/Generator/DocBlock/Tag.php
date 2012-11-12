<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Code
 */

namespace Zend\Code\Generator\DocBlock;

use Zend\Code\Generator\AbstractGenerator;
use Zend\Code\Reflection\DocBlock\Tag\TagInterface as ReflectionDocBlockTag;

/**
 * @category   Zend
 * @package    Zend_Code_Generator
 */
class Tag extends AbstractGenerator
{

    protected static $typeFormats = array(
        array(
            'param',
            '@param <type> <variable> <description>'
        ),
        array(
            'return',
            '@return <type> <description>'
        ),
        array(
            'tag',
            '@<name> <description>'
        )
    );

    /**
     * @var string
     */
    protected $name = null;

    /**
     * @var string
     */
    protected $description = null;

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        if (array_key_exists('name', $options)) {
            $this->setName($options['name']);
        }
        if (array_key_exists('description', $options)) {
            $this->setDescription($options['description']);
        }
    }

    /**
     * fromReflection()
     *
     * @param ReflectionDocBlockTag $reflectionTag
     * @return Tag
     */
    public static function fromReflection(ReflectionDocBlockTag $reflectionTag)
    {
        $tagName = $reflectionTag->getName();

        $codeGenDocBlockTag = new self();
        $codeGenDocBlockTag->setName($tagName);

        // transport any properties via accessors and mutators from reflection to codegen object
        $reflectionClass = new \ReflectionClass($reflectionTag);
        foreach ($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if (substr($method->getName(), 0, 3) == 'get') {
                $propertyName = substr($method->getName(), 3);
                if (method_exists($codeGenDocBlockTag, 'set' . $propertyName)) {
                    $codeGenDocBlockTag->{'set' . $propertyName}($reflectionTag->{'get' . $propertyName}());
                }
            }
        }

        return $codeGenDocBlockTag;
    }

    /**
     * setName()
     *
     * @param string $name
     * @return Tag
     */
    public function setName($name)
    {
        $this->name = ltrim($name, '@');
        return $this;
    }

    /**
     * getName()
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * setDescription()
     *
     * @param string $description
     * @return Tag
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * getDescription()
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * generate()
     *
     * @return string
     */
    public function generate()
    {
        $output = '@' . $this->name
            . (($this->description != null) ? ' ' . $this->description : '');
        return $output;
    }

}
