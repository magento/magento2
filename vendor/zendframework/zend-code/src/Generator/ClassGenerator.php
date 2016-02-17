<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Generator;

use Zend\Code\Reflection\ClassReflection;

class ClassGenerator extends AbstractGenerator
{
    const OBJECT_TYPE = "class";

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
     * @var PropertyGenerator[] Array of constants
     */
    protected $constants = array();

    /**
     * @var MethodGenerator[] Array of methods
     */
    protected $methods = array();

    /**
     * @var TraitUsageGenerator Object to encapsulate trait usage logic
     */
    protected $traitUsageGenerator;

    /**
     * Build a Code Generation Php Object from a Class Reflection
     *
     * @param  ClassReflection $classReflection
     * @return ClassGenerator
     */
    public static function fromReflection(ClassReflection $classReflection)
    {
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
        $interfaces  = $classReflection->getInterfaces();

        if ($parentClass) {
            $cg->setExtendedClass($parentClass->getName());

            $interfaces = array_diff($interfaces, $parentClass->getInterfaces());
        }

        $interfaceNames = array();
        foreach ($interfaces as $interface) {
            /* @var \Zend\Code\Reflection\ClassReflection $interface */
            $interfaceNames[] = $interface->getName();
        }

        $cg->setImplementedInterfaces($interfaceNames);

        $properties = array();

        foreach ($classReflection->getProperties() as $reflectionProperty) {
            if ($reflectionProperty->getDeclaringClass()->getName() == $classReflection->getName()) {
                $properties[] = PropertyGenerator::fromReflection($reflectionProperty);
            }
        }

        $cg->addProperties($properties);

        $constants = array();

        foreach ($classReflection->getConstants() as $name => $value) {
            $constants[] = array(
                'name' => $name,
                'value' => $value
            );
        }

        $cg->addConstants($constants);

        $methods = array();

        foreach ($classReflection->getMethods() as $reflectionMethod) {
            $className = ($cg->getNamespaceName()) ? $cg->getNamespaceName() . "\\" . $cg->getName() : $cg->getName();

            if ($reflectionMethod->getDeclaringClass()->getName() == $className) {
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
     * @throws Exception\InvalidArgumentException
     * @param  array $array
     * @return ClassGenerator
     */
    public static function fromArray(array $array)
    {
        if (!isset($array['name'])) {
            throw new Exception\InvalidArgumentException(
                'Class generator requires that a name is provided for this object'
            );
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
                    $docBlock = ($value instanceof DocBlockGenerator) ? $value : DocBlockGenerator::fromArray($value);
                    $cg->setDocBlock($docBlock);
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

    /**
     * @param  string $name
     * @param  string $namespaceName
     * @param  array|string $flags
     * @param  string $extends
     * @param  array $interfaces
     * @param  array $properties
     * @param  array $methods
     * @param  DocBlockGenerator $docBlock
     */
    public function __construct(
        $name = null,
        $namespaceName = null,
        $flags = null,
        $extends = null,
        $interfaces = array(),
        $properties = array(),
        $methods = array(),
        $docBlock = null
    ) {
        $this->traitUsageGenerator = new TraitUsageGenerator($this);

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
     * @param  string $name
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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param  string $namespaceName
     * @return ClassGenerator
     */
    public function setNamespaceName($namespaceName)
    {
        $this->namespaceName = $namespaceName;
        return $this;
    }

    /**
     * @return string
     */
    public function getNamespaceName()
    {
        return $this->namespaceName;
    }

    /**
     * @param  FileGenerator $fileGenerator
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
     * @param  DocBlockGenerator $docBlock
     * @return ClassGenerator
     */
    public function setDocBlock(DocBlockGenerator $docBlock)
    {
        $this->docBlock = $docBlock;
        return $this;
    }

    /**
     * @return DocBlockGenerator
     */
    public function getDocBlock()
    {
        return $this->docBlock;
    }

    /**
     * @param  array|string $flags
     * @return ClassGenerator
     */
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

    /**
     * @param  string $flag
     * @return ClassGenerator
     */
    public function addFlag($flag)
    {
        $this->setFlags($this->flags | $flag);
        return $this;
    }

    /**
     * @param  string $flag
     * @return ClassGenerator
     */
    public function removeFlag($flag)
    {
        $this->setFlags($this->flags & ~$flag);
        return $this;
    }

    /**
     * @param  bool $isAbstract
     * @return ClassGenerator
     */
    public function setAbstract($isAbstract)
    {
        return (($isAbstract) ? $this->addFlag(self::FLAG_ABSTRACT) : $this->removeFlag(self::FLAG_ABSTRACT));
    }

    /**
     * @return bool
     */
    public function isAbstract()
    {
        return (bool) ($this->flags & self::FLAG_ABSTRACT);
    }

    /**
     * @param  bool $isFinal
     * @return ClassGenerator
     */
    public function setFinal($isFinal)
    {
        return (($isFinal) ? $this->addFlag(self::FLAG_FINAL) : $this->removeFlag(self::FLAG_FINAL));
    }

    /**
     * @return bool
     */
    public function isFinal()
    {
        return ($this->flags & self::FLAG_FINAL);
    }

    /**
     * @param  string $extendedClass
     * @return ClassGenerator
     */
    public function setExtendedClass($extendedClass)
    {
        $this->extendedClass = $extendedClass;
        return $this;
    }

    /**
     * @return string
     */
    public function getExtendedClass()
    {
        return $this->extendedClass;
    }

    /**
     * @param  array $implementedInterfaces
     * @return ClassGenerator
     */
    public function setImplementedInterfaces(array $implementedInterfaces)
    {
        $this->implementedInterfaces = $implementedInterfaces;
        return $this;
    }

    /**
     * @return array
     */
    public function getImplementedInterfaces()
    {
        return $this->implementedInterfaces;
    }

    /**
     * @param  string $constantName
     *
     * @return PropertyGenerator|false
     */
    public function getConstant($constantName)
    {
        if (isset($this->constants[$constantName])) {
            return $this->constants[$constantName];
        }

        return false;
    }

    /**
     * @return PropertyGenerator[] indexed by constant name
     */
    public function getConstants()
    {
        return $this->constants;
    }

    /**
     * @param  string $constantName
     * @return bool
     */
    public function hasConstant($constantName)
    {
        return isset($this->constants[$constantName]);
    }

    /**
     * Add constant from PropertyGenerator
     *
     * @param  PropertyGenerator           $constant
     * @throws Exception\InvalidArgumentException
     * @return ClassGenerator
     */
    public function addConstantFromGenerator(PropertyGenerator $constant)
    {
        $constantName = $constant->getName();

        if (isset($this->constants[$constantName])) {
            throw new Exception\InvalidArgumentException(sprintf(
                'A constant by name %s already exists in this class.',
                $constantName
            ));
        }

        if (! $constant->isConst()) {
            throw new Exception\InvalidArgumentException(sprintf(
                'The value %s is not defined as a constant.',
                $constantName
            ));
        }

        $this->constants[$constantName] = $constant;

        return $this;
    }

    /**
     * Add Constant
     *
     * @param  string $name
     * @param  string $value
     * @throws Exception\InvalidArgumentException
     * @return ClassGenerator
     */
    public function addConstant($name, $value)
    {
        if (!is_string($name)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects string for name',
                __METHOD__
            ));
        }

        if (empty($value) || !is_string($value)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects value for constant, value must be a string',
                __METHOD__
            ));
        }

        return $this->addConstantFromGenerator(new PropertyGenerator($name, $value, PropertyGenerator::FLAG_CONSTANT));
    }

    /**
     * @param  PropertyGenerator[]|array[] $constants
     *
     * @return ClassGenerator
     */
    public function addConstants(array $constants)
    {
        foreach ($constants as $constant) {
            if ($constant instanceof PropertyGenerator) {
                $this->addPropertyFromGenerator($constant);
            } else {
                if (is_array($constant)) {
                    call_user_func_array(array($this, 'addConstant'), $constant);
                }
            }
        }

        return $this;
    }

    /**
     * @param  array $properties
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
     * @param  string $name
     * @param  string|array $defaultValue
     * @param  int $flags
     * @throws Exception\InvalidArgumentException
     * @return ClassGenerator
     */
    public function addProperty($name, $defaultValue = null, $flags = PropertyGenerator::FLAG_PUBLIC)
    {
        if (!is_string($name)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s::%s expects string for name',
                get_class($this),
                __FUNCTION__
            ));
        }

        // backwards compatibility
        // @todo remove this on next major version
        if ($flags === PropertyGenerator::FLAG_CONSTANT) {
            return $this->addConstant($name, $defaultValue);
        }

        return $this->addPropertyFromGenerator(new PropertyGenerator($name, $defaultValue, $flags));
    }

    /**
     * Add property from PropertyGenerator
     *
     * @param  PropertyGenerator           $property
     * @throws Exception\InvalidArgumentException
     * @return ClassGenerator
     */
    public function addPropertyFromGenerator(PropertyGenerator $property)
    {
        $propertyName = $property->getName();

        if (isset($this->properties[$propertyName])) {
            throw new Exception\InvalidArgumentException(sprintf(
                'A property by name %s already exists in this class.',
                $propertyName
            ));
        }

        // backwards compatibility
        // @todo remove this on next major version
        if ($property->isConst()) {
            return $this->addConstantFromGenerator($property);
        }

        $this->properties[$propertyName] = $property;
        return $this;
    }

    /**
     * @return PropertyGenerator[]
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param  string $propertyName
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
     * Add a class to "use" classes
     *
     * @param  string $use
     * @param  string|null $useAlias
     * @return ClassGenerator
     */
    public function addUse($use, $useAlias = null)
    {
        $this->traitUsageGenerator->addUse($use, $useAlias);
        return $this;
    }

    /**
     * Returns the "use" classes
     *
     * @return array
     */
    public function getUses()
    {
        return $this->traitUsageGenerator->getUses();
    }

    /**
     * @param  string $propertyName
     * @return bool
     */
    public function hasProperty($propertyName)
    {
        return isset($this->properties[$propertyName]);
    }

    /**
     * @param  array $methods
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
     * @param  string $name
     * @param  array $parameters
     * @param  int $flags
     * @param  string $body
     * @param  string $docBlock
     * @throws Exception\InvalidArgumentException
     * @return ClassGenerator
     */
    public function addMethod(
        $name = null,
        array $parameters = array(),
        $flags = MethodGenerator::FLAG_PUBLIC,
        $body = null,
        $docBlock = null
    ) {
        if (!is_string($name)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s::%s expects string for name',
                get_class($this),
                __FUNCTION__
            ));
        }

        return $this->addMethodFromGenerator(new MethodGenerator($name, $parameters, $flags, $body, $docBlock));
    }

    /**
     * Add Method from MethodGenerator
     *
     * @param  MethodGenerator                    $method
     * @throws Exception\InvalidArgumentException
     * @return ClassGenerator
     */
    public function addMethodFromGenerator(MethodGenerator $method)
    {
        $methodName = $method->getName();

        if ($this->hasMethod($methodName)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'A method by name %s already exists in this class.',
                $methodName
            ));
        }

        $this->methods[strtolower($methodName)] = $method;
        return $this;
    }

    /**
     * @return MethodGenerator[]
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @param  string $methodName
     * @return MethodGenerator|false
     */
    public function getMethod($methodName)
    {
        return $this->hasMethod($methodName) ? $this->methods[strtolower($methodName)] : false;
    }

    /**
     * @param  string $methodName
     * @return ClassGenerator
     */
    public function removeMethod($methodName)
    {
        if ($this->hasMethod($methodName)) {
            unset($this->methods[strtolower($methodName)]);
        }

        return $this;
    }

    /**
     * @param  string $methodName
     * @return bool
     */
    public function hasMethod($methodName)
    {
        return isset($this->methods[strtolower($methodName)]);
    }

    /**
     * @inherit Zend\Code\Generator\TraitUsageInterface
     */
    public function addTrait($trait)
    {
        $this->traitUsageGenerator->addTrait($trait);
        return $this;
    }

    /**
     * @inherit Zend\Code\Generator\TraitUsageInterface
     */
    public function addTraits(array $traits)
    {
        $this->traitUsageGenerator->addTraits($traits);
        return $this;
    }

    /**
     * @inherit Zend\Code\Generator\TraitUsageInterface
     */
    public function hasTrait($traitName)
    {
        return $this->traitUsageGenerator->hasTrait($traitName);
    }

    /**
     * @inherit Zend\Code\Generator\TraitUsageInterface
     */
    public function getTraits()
    {
        return $this->traitUsageGenerator->getTraits();
    }

    /**
     * @inherit Zend\Code\Generator\TraitUsageInterface
     */
    public function removeTrait($traitName)
    {
        return $this->traitUsageGenerator->removeTrait($traitName);
    }

    /**
     * @inherit Zend\Code\Generator\TraitUsageInterface
     */
    public function addTraitAlias($method, $alias, $visibility = null)
    {
        $this->traitUsageGenerator->addTraitAlias($method, $alias, $visibility);
        return $this;
    }

    /**
     * @inherit Zend\Code\Generator\TraitUsageInterface
     */
    public function getTraitAliases()
    {
        return $this->traitUsageGenerator->getTraitAliases();
    }

    /**
     * @inherit Zend\Code\Generator\TraitUsageInterface
     */
    public function addTraitOverride($method, $traitsToReplace)
    {
        $this->traitUsageGenerator->addTraitOverride($method, $traitsToReplace);
        return $this;
    }

    /**
     * @inherit Zend\Code\Generator\TraitUsageInterface
     */
    public function removeTraitOverride($method, $overridesToRemove = null)
    {
        $this->traitUsageGenerator->removeTraitOverride($method, $overridesToRemove);

        return $this;
    }

    /**
     * @inherit Zend\Code\Generator\TraitUsageInterface
     */
    public function getTraitOverrides()
    {
        return $this->traitUsageGenerator->getTraitOverrides();
    }

    /**
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
     * @inherit Zend\Code\Generator\GeneratorInterface
     */
    public function generate()
    {
        if (!$this->isSourceDirty()) {
            $output = $this->getSourceContent();
            if (!empty($output)) {
                return $output;
            }
        }

        $indent = $this->getIndentation();
        $output = '';

        if (null !== ($namespace = $this->getNamespaceName())) {
            $output .= 'namespace ' . $namespace . ';' . self::LINE_FEED . self::LINE_FEED;
        }

        $uses = $this->getUses();

        if (!empty($uses)) {
            foreach ($uses as $use) {
                $output .= 'use ' . $use . ';' . self::LINE_FEED;
            }

            $output .= self::LINE_FEED;
        }

        if (null !== ($docBlock = $this->getDocBlock())) {
            $docBlock->setIndentation('');
            $output .= $docBlock->generate();
        }

        if ($this->isAbstract()) {
            $output .= 'abstract ';
        } elseif ($this->isFinal()) {
            $output .= 'final ';
        }

        $output .= static::OBJECT_TYPE . ' ' . $this->getName();

        if (!empty($this->extendedClass)) {
            $output .= ' extends ' . $this->extendedClass;
        }

        $implemented = $this->getImplementedInterfaces();

        if (!empty($implemented)) {
            $output .= ' implements ' . implode(', ', $implemented);
        }

        $output .= self::LINE_FEED . '{' . self::LINE_FEED . self::LINE_FEED;
        $output .= $this->traitUsageGenerator->generate();

        $constants = $this->getConstants();

        foreach ($constants as $constant) {
            $output .= $constant->generate() . self::LINE_FEED . self::LINE_FEED;
        }

        $properties = $this->getProperties();

        foreach ($properties as $property) {
            $output .= $property->generate() . self::LINE_FEED . self::LINE_FEED;
        }

        $methods = $this->getMethods();

        foreach ($methods as $method) {
            $output .= $method->generate() . self::LINE_FEED;
        }

        $output .= self::LINE_FEED . '}' . self::LINE_FEED;

        return $output;
    }
}
