<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Generator\DocBlock;

use ReflectionClass;
use ReflectionMethod;
use Zend\Code\Generator\AbstractGenerator;
use Zend\Code\Reflection\DocBlock\Tag\TagInterface as ReflectionDocBlockTag;

class Tag extends AbstractGenerator
{
    /**
     * @var string
     */
    protected $name = null;

    /**
     * @var string
     */
    protected $description = null;

    /**
     * Build a Tag generator object from a reflection object
     *
     * @param  ReflectionDocBlockTag $reflectionTag
     * @return Tag
     */
    public static function fromReflection(ReflectionDocBlockTag $reflectionTag)
    {
        $tagName = $reflectionTag->getName();

        $codeGenDocBlockTag = new static();
        $codeGenDocBlockTag->setName($tagName);

        // transport any properties via accessors and mutators from reflection to codegen object
        $reflectionClass = new ReflectionClass($reflectionTag);
        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
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
     * @param  string $name
     * @return Tag
     */
    public function setName($name)
    {
        $this->name = ltrim($name, '@');
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param  string $description
     * @return Tag
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function generate()
    {
        $output = '@' . $this->name
            . (($this->description != null) ? ' ' . $this->description : '');

        return $output;
    }
}
