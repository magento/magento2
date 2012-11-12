<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Code
 */

namespace Zend\Code\Generator;

use Zend\Code\Reflection\ClassReflection;

/**
 * @category   Zend
 * @package    Zend_Code_Generator
 */
class ClassGenerator extends AbstractGenerator
{

    const FLAG_ABSTRACT = 0x01;
    const FLAG_FINAL    = 0x02;

    /**
     * @var FileGenerator
     */
    protected $containingFileGenerator = null;

    /**
     * @var string
     */
    protected $namespaceName = null;

    /**
     * @var DocBlockGenerator
     */
    protected $docBlock = null;

    /**
     * @var string
     */
    protected $name = null;

    /**
     * @var bool
     */
    protected $flags = 0x00;

    /**
     * @var string
     */
    protected $extendedClass = null;

    /**
     * @var array Array of string names
     */
    protected $implementedInterfaces = array();

    /**
     * @var PropertyGenerator[] Array of properties
     */
    protected $properties = array();

    /**
     * @var MethodGenerator[] Array of methods
     */
    protected $methods = array();

    /**
     * fromReflection() - build a Code Generation Php Object from a Class Reflection
     *
     * @param ClassReflection $classReflection
     * @return ClassGenerator
     */
    public static function fromReflection(ClassReflection $classReflection)
    {
        // class generator
        $cg = new static($classReflection->getName());

        $cg->setSourceContent($cg->getSourceContent());
        $cg->setSourceDirty(false);

        if ($classReflection->getDocComment() != '') {
            $cg->setDocBlock(DocBlockGenerator::fromReflection($classReflection->getDocBlock()));
        }

        $cg->setAbstract($classReflection->isAbstract());

        // set the namespace
        if ($classReflection->inNamespace()) {
            $cg->setNamespaceName($classReflection->getNamespaceName());
        }

        /* @var \Zend\Code\Reflection\ClassReflection $parentClass */
        $parentClass = $classReflection->getParentClass();
        if ($parentClass) {
            $cg->setExtendedClass($parentClass->getName());
            $interfaces = array_diff($classReflection->getInterfaces(), $parentClass->getInterfaces());
        } else {
            $interfaces = $classReflection->getInterfaces();
        }

        $interfaceNames = array();
        foreach ($interfaces AS $interface) {
            /* @var \Zend\Code\Reflection\ClassReflection $interface */
            $interfaceNames[] = $interface->getName();
        }

        $cg->setImplementedInterfaces($interfaceNames);

        $properties = array();
        foreach ($classReflection->getProperties() as $reflectionProperty) {
            if ($reflectionProperty->getDeclaringClass()->getName() == $cg->getName()) {
                $properties[] = PropertyGenerator::fromReflection($reflectionProperty);
            }
        }
        $cg->addProperties($properties);

        $methods = array();
        foreach ($classReflection->getMethods() as $reflectionMethod) {
            if ($reflectionMethod->getDeclaringClass()->getName() == $cg->getNamespaceName() . "\\" . $cg->getName()) {
                $methods[] = MethodGenerator::fromReflection($reflectionMethod);
            }
        }
        $cg->addMethods($methods);

        return $cg;
    }

    /**
     * Generate from array
     *
     * @configkey name           string        [required] Class Name
     * @configkey filegenerator  FileGenerator File generator that holds this class
     * @configkey namespacename  string        The namespace for this class
     * @configkey docblock       string        The docblock information
     * @configkey flags          int           Flags, one of ClassGenerator::FLAG_ABSTRACT ClassGenerator::FLAG_FINAL
     * @configkey extendedclass  string        Class which this class is extending
     * @configkey implementedinterfaces
     * @configkey properties
     * @configkey methods
     *
     *
     * @throws Exception\InvalidArgumentException
     * @param array $array
     * @return ClassGenerator
     */
    public static function fromArray(array $array)
    {
        if (!isset($array['name'])) {
            throw new Exception\InvalidArgumentException('Class generator requires that a name is provided for this object');
        }
        $cg = new static($array['name']);
        foreach ($array as $name => $value) {
            // normalize key
            switch (strtolower(str_replace(array('.', '-', '_'), '', $name))) {
                case 'containingfile':
                    $cg->setContainingFileGenerator($value);
                    break;
                case 'namespacename':
                    $cg->setNamespaceName($value);
                    break;
                case 'docblock':
                    $cg->setDocBlock((!$value instanceof DocBlockGenerator) ? : DocBlockGenerator::fromArray($value));
                    break;
                case 'flags':
                    $cg->setFlags($value);
                    break;
                case 'extendedclass':
                    $cg->setExtendedClass($value);
                    break;
                case 'implementedinterfaces':
                    $cg->setImplementedInterfaces($value);
                    break;
                case 'properties':
                    $cg->addProperties($value);
                    break;
                case 'methods':
                    $cg->addMethods($value);
                    break;
            }
        }
        return $cg;
    }

    public function __construct($name = null, $namespaceName = null, $flags = null, $extends = null,
                                $interfaces = array(), $properties = array(), $methods = array(), $docBlock = null)
    {
        if ($name !== null) {
            $this->setName($name);
        }
        if ($namespaceName !== null) {
            $this->setNamespaceName($namespaceName);
        }
        if ($flags !== null) {
            $this->setFlags($flags);
        }
        if ($properties !== array()) {
            $this->addProperties($properties);
        }
        if ($extends !== null) {
            $this->setExtendedClass($extends);
        }
        if (is_array($interfaces)) {
            $this->setImplementedInterfaces($interfaces);
        }
        if ($methods !== array()) {
            $this->addMethods($methods);
        }
        if ($docBlock !== null) {
            $this->setDocBlock($docBlock);
        }
    }

    /**
     * setName()
     *
     * @param string $name
     * @return ClassGenerator
     */
    public function setName($name)
    {
        if (strstr($name, '\\')) {
            $namespace = substr($name, 0, strrpos($name, '\\'));
            $name      = substr($name, strrpos($name, '\\') + 1);
            $this->setNamespaceName($namespace);
        }

        $this->name = $name;
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
     * getNamespaceName()
     *
     * @return string
     */
    public function getNamespaceName()
    {
        return $this->namespaceName;
    }

    /**
     * setNamespaceName()
     *
     * @param $namespaceName
     * @return ClassGenerator
     */
    public function setNamespaceName($namespaceName)
    {
        $this->namespaceName = $namespaceName;

        return $this;
    }

    /**
     * @param FileGenerator $fileGenerator
     * @return ClassGenerator
     */
    public function setContainingFileGenerator(FileGenerator $fileGenerator)
    {
        $this->containingFileGenerator = $fileGenerator;

        return $this;
    }

    /**
     * @return FileGenerator
     */
    public function getContainingFileGenerator()
    {
        return $this->containingFileGenerator;
    }

    /**
     * @param DocBlockGenerator $docBlock
     * @return ClassGenerator
     */
    public function setDocBlock(DocBlockGenerator $docBlock)
    {
        $this->docBlock = $docBlock;

        return $this;
    }


    /**
     * getDocBlock()
     *
     * @return DocBlockGenerator
     */
    public function getDocBlock()
    {
        return $this->docBlock;
    }

    public function setFlags($flags)
    {
        if (is_array($flags)) {
            $flagsArray = $flags;
            $flags      = 0x00;
            foreach ($flagsArray as $flag) {
                $flags |= $flag;
            }
        }
        // check that visibility is one of three
        $this->flags = $flags;
        return $this;
    }

    public function addFlag($flag)
    {
        $this->setFlags($this->flags | $flag);
        return $this;
    }

    public function removeFlag($flag)
    {
        $this->setFlags($this->flags & ~$flag);
        return $this;
    }

    /**
     * setAbstract()
     *
     * @param bool $isAbstract
     * @return AbstractMemberGenerator
     */
    public function setAbstract($isAbstract)
    {
        return (($isAbstract) ? $this->addFlag(self::FLAG_ABSTRACT) : $this->removeFlag(self::FLAG_ABSTRACT));
    }

    /**
     * isAbstract()
     *
     * @return bool
     */
    public function isAbstract()
    {
        return (boolean)($this->flags & self::FLAG_ABSTRACT);
    }

    /**
     * setFinal()
     *
     * @param bool $isFinal
     * @return AbstractMemberGenerator
     */
    public function setFinal($isFinal)
    {
        return (($isFinal) ? $this->addFlag(self::FLAG_FINAL) : $this->removeFlag(self::FLAG_FINAL));
    }

    /**
     * isFinal()
     *
     * @return bool
     */
    public function isFinal()
    {
        return ($this->flags & self::FLAG_FINAL);
    }

    /**
     * setExtendedClass()
     *
     * @param string $extendedClass
     * @return ClassGenerator
     */
    public function setExtendedClass($extendedClass)
    {
        $this->extendedClass = $extendedClass;
        return $this;
    }

    /**
     * getExtendedClass()
     *
     * @return string
     */
    public function getExtendedClass()
    {
        return $this->extendedClass;
    }

    /**
     * setImplementedInterfaces()
     *
     * @param array $implementedInterfaces
     * @return ClassGenerator
     */
    public function setImplementedInterfaces(array $implementedInterfaces)
    {
        $this->implementedInterfaces = $implementedInterfaces;
        return $this;
    }

    /**
     * getImplementedInterfaces
     *
     * @return array
     */
    public function getImplementedInterfaces()
    {
        return $this->implementedInterfaces;
    }

    /**
     * addProperties()
     *
     * @param array $properties
     * @return ClassGenerator
     */
    public function addProperties(array $properties)
    {
        foreach ($properties as $property) {
            if ($property instanceof PropertyGenerator) {
                $this->addPropertyFromGenerator($property);
            } else {
                if (is_string($property)) {
                    $this->addProperty($property);
                } elseif (is_array($property)) {
                    call_user_func_array(array($this, 'addProperty'), $property);
                }
            }
        }

        return $this;
    }

    /**
     * Add Property from scalars
     *
     * @param string       $name
     * @param string|array $defaultValue
     * @param int          $flags
     * @throws Exception\InvalidArgumentException
     * @return ClassGenerator
     */
    public function addProperty($name, $defaultValue = null, $flags = PropertyGenerator::FLAG_PUBLIC)
    {
        if (!is_string($name)) {
            throw new Exception\InvalidArgumentException(
                'addProperty() expects string for name'
            );
        }

        return $this->addPropertyFromGenerator(new PropertyGenerator($name, $defaultValue, $flags));
    }

    /**
     * add property from PropertyGenerator
     *
     * @param  string|PropertyGenerator $property
     * @throws Exception\InvalidArgumentException
     * @return ClassGenerator
     */
    public function addPropertyFromGenerator(PropertyGenerator $property)
    {
        $propertyName = $property->getName();

        if (isset($this->properties[$propertyName])) {
            throw new Exception\InvalidArgumentException('A property by name ' . $propertyName . ' already exists in this class.');
        }

        $this->properties[$propertyName] = $property;
        return $this;
    }

    /**
     * getProperties()
     *
     * @return PropertyGenerator[]
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * getProperty()
     *
     * @param string $propertyName
     * @return PropertyGenerator|false
     */
    public function getProperty($propertyName)
    {
        foreach ($this->getProperties() as $property) {
            if ($property->getName() == $propertyName) {
                return $property;
            }
        }
        return false;
    }

    /**
     * hasProperty()
     *
     * @param string $propertyName
     * @return bool
     */
    public function hasProperty($propertyName)
    {
        return isset($this->properties[$propertyName]);
    }

    /**
     * addMethods()
     *
     * @param array $methods
     * @return ClassGenerator
     */
    public function addMethods(array $methods)
    {
        foreach ($methods as $method) {
            if ($method instanceof MethodGenerator) {
                $this->addMethodFromGenerator($method);
            } else {
                if (is_string($method)) {
                    $this->addMethod($method);
                } elseif (is_array($method)) {
                    call_user_func_array(array($this, 'addMethod'), $method);
                }
            }
        }
        return $this;
    }

    /**
     * Add Method from scalars
     *
     * @param string  $name
     * @param array $parameters
     * @param int   $flags
     * @param string  $body
     * @param string  $docBlock
     * @throws Exception\InvalidArgumentException
     * @return ClassGenerator
     */
    public function addMethod($name = null, array $parameters = array(), $flags = MethodGenerator::FLAG_PUBLIC,
                              $body = null, $docBlock = null)
    {
        if (!is_string($name)) {
            throw new Exception\InvalidArgumentException(
                'addMethod() expects string for name'
            );
        }

        return $this->addMethodFromGenerator(new MethodGenerator($name, $parameters, $flags, $body, $docBlock));
    }


    /**
     * Add Method from MethodGenerator
     *
     * @param  MethodGenerator $method
     * @throws Exception\InvalidArgumentException
     * @return ClassGenerator
     */
    public function addMethodFromGenerator(MethodGenerator $method)
    {
        $methodName = $method->getName();

        if (isset($this->methods[$methodName])) {
            throw new Exception\InvalidArgumentException('A method by name ' . $methodName . ' already exists in this class.');
        }

        $this->methods[$methodName] = $method;
        return $this;
    }

    /**
     * getMethods()
     *
     * @return MethodGenerator[]
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * getMethod()
     *
     * @param string $methodName
     * @return MethodGenerator|false
     */
    public function getMethod($methodName)
    {
        foreach ($this->getMethods() as $method) {
            if ($method->getName() == $methodName) {
                return $method;
            }
        }
        return false;
    }

    /**
     * hasMethod()
     *
     * @param string $methodName
     * @return bool
     */
    public function hasMethod($methodName)
    {
        return isset($this->methods[$methodName]);
    }

    /**
     * isSourceDirty()
     *
     * @return bool
     */
    public function isSourceDirty()
    {
        if (($docBlock = $this->getDocBlock()) && $docBlock->isSourceDirty()) {
            return true;
        }

        foreach ($this->getProperties() as $property) {
            if ($property->isSourceDirty()) {
                return true;
            }
        }

        foreach ($this->getMethods() as $method) {
            if ($method->isSourceDirty()) {
                return true;
            }
        }

        return parent::isSourceDirty();
    }

    /**
     * generate()
     *
     * @return string
     */
    public function generate()
    {
        if (!$this->isSourceDirty()) {
            $output = $this->getSourceContent();
            if (!empty($output)) {
                return $output;
            }
        }

        $output = '';

        if (null !== ($namespace = $this->getNamespaceName())) {
            $output .= 'namespace ' . $namespace . ';' . self::LINE_FEED . self::LINE_FEED;
        }

        if (null !== ($docBlock = $this->getDocBlock())) {
            $docBlock->setIndentation('');
            $output .= $docBlock->generate();
        }

        if ($this->isAbstract()) {
            $output .= 'abstract ';
        }

        $output .= 'class ' . $this->getName();

        if (!empty($this->extendedClass)) {
            $output .= ' extends ' . $this->extendedClass;
        }

        $implemented = $this->getImplementedInterfaces();
        if (!empty($implemented)) {
            $output .= ' implements ' . implode(', ', $implemented);
        }

        $output .= self::LINE_FEED . '{' . self::LINE_FEED . self::LINE_FEED;

        $properties = $this->getProperties();
        if (!empty($properties)) {
            foreach ($properties as $property) {
                $output .= $property->generate() . self::LINE_FEED . self::LINE_FEED;
            }
        }

        $methods = $this->getMethods();
        if (!empty($methods)) {
            foreach ($methods as $method) {
                $output .= $method->generate() . self::LINE_FEED;
            }
        }

        $output .= self::LINE_FEED . '}' . self::LINE_FEED;

        return $output;
    }

}
