<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Di
 */

namespace Zend\Di\Definition;

use Zend\Code\Annotation\AnnotationCollection;
use Zend\Code\Reflection;
use Zend\Code\Scanner\AggregateDirectoryScanner;
use Zend\Code\Scanner\DerivedClassScanner;
use Zend\Code\Scanner\DirectoryScanner;
use Zend\Code\Scanner\FileScanner;
use Zend\Di\Definition\Annotation;

/**
 * Class definitions based on a set of directories to be scanned
 *
 * @category   Zend
 * @package    Zend_Di
 */
class CompilerDefinition implements DefinitionInterface
{
    protected $isCompiled = false;

    protected $introspectionStrategy = null;

    protected $allowReflectionExceptions = false;

    /**
     * @var AggregateDirectoryScanner
     */
    protected $directoryScanner = null;

    protected $classes = array();

    /**
     * Constructor
     *
     * @param null|IntrospectionStrategy $introspectionStrategy
     */
    public function __construct(IntrospectionStrategy $introspectionStrategy = null)
    {
        $this->introspectionStrategy = ($introspectionStrategy) ?: new IntrospectionStrategy();
        $this->directoryScanner = new AggregateDirectoryScanner();
    }

    /**
     * Set introspection strategy
     *
     * @param IntrospectionStrategy $introspectionStrategy
     */
    public function setIntrospectionStrategy(IntrospectionStrategy $introspectionStrategy)
    {
        $this->introspectionStrategy = $introspectionStrategy;
    }

    /**
     * @param bool $allowReflectionExceptions
     */
    public function setAllowReflectionExceptions($allowReflectionExceptions = true)
    {
        $this->allowReflectionExceptions = (bool) $allowReflectionExceptions;
    }

    /**
     * Get introspection strategy
     *
     * @return IntrospectionStrategy
     */
    public function getIntrospectionStrategy()
    {
        return $this->introspectionStrategy;
    }

    /**
     * Add directory
     *
     * @param string $directory
     */
    public function addDirectory($directory)
    {
        $this->addDirectoryScanner(new DirectoryScanner($directory));
    }

    /**
     * Add directory scanner
     *
     * @param DirectoryScanner $directoryScanner
     */
    public function addDirectoryScanner(DirectoryScanner $directoryScanner)
    {
        $this->directoryScanner->addDirectoryScanner($directoryScanner);
    }

    /**
     * Add code scanner file
     *
     * @param FileScanner $fileScanner
     */
    public function addCodeScannerFile(FileScanner $fileScanner)
    {
        if ($this->directoryScanner == null) {
            $this->directoryScanner = new DirectoryScanner();
        }

        $this->directoryScanner->addFileScanner($fileScanner);
    }

    /**
     * Compile
     *
     * @return void
     */
    public function compile()
    {
        /* @var $classScanner \Zend\Code\Scanner\DerivedClassScanner */
        foreach ($this->directoryScanner->getClassNames() as $class) {
            $this->processClass($class);
        }
    }

    /**
     * @return ArrayDefinition
     */
    public function toArrayDefinition()
    {
        return new ArrayDefinition(
            $this->classes
        );
    }

    /**
     * @param  string               $class
     * @throws \ReflectionException
     */
    protected function processClass($class)
    {
        $strategy = $this->introspectionStrategy; // localize for readability

        try {
            $rClass = new Reflection\ClassReflection($class);
        } catch (\ReflectionException $e) {
            if (!$this->allowReflectionExceptions) {
                throw $e;
            }

            return;
        }
        $className = $rClass->getName();
        $matches = null; // used for regex below

        // setup the key in classes
        $this->classes[$className] = array(
            'supertypes'   => array(),
            'instantiator' => null,
            'methods'      => array(),
            'parameters'   => array()
        );

        $def = &$this->classes[$className]; // localize for brevity

        // class annotations?
        if ($strategy->getUseAnnotations() == true) {
            $annotations = $rClass->getAnnotations($strategy->getAnnotationManager());

            if (($annotations instanceof AnnotationCollection)
                && $annotations->hasAnnotation('Zend\Di\Definition\Annotation\Instantiator')
            ) {
                // @todo Instantiator support in annotations
            }
        }

        /* @var $rTarget \Zend\Code\Reflection\ClassReflection */
        $rTarget = $rClass;
        $supertypes = array();
        do {
            $supertypes = array_merge($supertypes, $rTarget->getInterfaceNames());
            if (!($rTargetParent = $rTarget->getParentClass())) {
                break;
            }
            $supertypes[] = $rTargetParent->getName();
            $rTarget = $rTargetParent;
        } while (true);

        $def['supertypes'] = $supertypes;

        if ($def['instantiator'] == null) {
            if ($rClass->isInstantiable()) {
                $def['instantiator'] = '__construct';
            }
        }

        if ($rClass->hasMethod('__construct')) {
            $def['methods']['__construct'] = true; // required
            try {
                $this->processParams($def, $rClass, $rClass->getMethod('__construct'));
            } catch (\ReflectionException $e) {
                if (!$this->allowReflectionExceptions) {
                    throw $e;
                }

                return;
            }
        }

        foreach ($rClass->getMethods(Reflection\MethodReflection::IS_PUBLIC) as $rMethod) {

            $methodName = $rMethod->getName();

            if ($rMethod->getName() === '__construct') {
                continue;
            }

            if ($strategy->getUseAnnotations() == true) {
                $annotations = $rMethod->getAnnotations($strategy->getAnnotationManager());

                if (($annotations instanceof AnnotationCollection)
                    && $annotations->hasAnnotation('Zend\Di\Definition\Annotation\Inject')
                ) {

                    $def['methods'][$methodName] = true;
                    $this->processParams($def, $rClass, $rMethod);
                    continue;
                }
            }

            $methodPatterns = $this->introspectionStrategy->getMethodNameInclusionPatterns();

            // matches a method injection pattern?
            foreach ($methodPatterns as $methodInjectorPattern) {
                preg_match($methodInjectorPattern, $methodName, $matches);
                if ($matches) {
                    $def['methods'][$methodName] = false; // check ot see if this is required?
                    $this->processParams($def, $rClass, $rMethod);
                    continue 2;
                }
            }

            // method
            // by annotation
            // by setter pattern,
            // by interface

        }

        $interfaceInjectorPatterns = $this->introspectionStrategy->getInterfaceInjectionInclusionPatterns();

        // matches the interface injection pattern
        /** @var $rIface \ReflectionClass */
        foreach ($rClass->getInterfaces() as $rIface) {
            foreach ($interfaceInjectorPatterns as $interfaceInjectorPattern) {
                preg_match($interfaceInjectorPattern, $rIface->getName(), $matches);
                if ($matches) {
                    foreach ($rIface->getMethods() as $rMethod) {
                        if ($rMethod->getName() === '__construct') {
                            // constructor not allowed in interfaces
                            continue;
                        }
                        $def['methods'][$rMethod->getName()] = true;
                        $this->processParams($def, $rClass, $rMethod);
                    }
                    continue 2;
                }
            }
        }
    }

    /**
     * @param array                                  $def
     * @param \Zend\Code\Reflection\ClassReflection  $rClass
     * @param \Zend\Code\Reflection\MethodReflection $rMethod
     */
    protected function processParams(&$def, Reflection\ClassReflection $rClass, Reflection\MethodReflection $rMethod)
    {
        if (count($rMethod->getParameters()) === 0) {
            return;
        }

        $methodName = $rMethod->getName();

        // @todo annotations here for alternate names?

        $def['parameters'][$methodName] = array();

        foreach ($rMethod->getParameters() as $p) {

            /** @var $p \ReflectionParameter  */
            $actualParamName = $p->getName();

            $fqName = $rClass->getName() . '::' . $rMethod->getName() . ':' . $p->getPosition();

            $def['parameters'][$methodName][$fqName] = array();

            // set the class name, if it exists
            $def['parameters'][$methodName][$fqName][] = $actualParamName;
            $def['parameters'][$methodName][$fqName][] = ($p->getClass() !== null) ? $p->getClass()->getName() : null;
            $def['parameters'][$methodName][$fqName][] = !$p->isOptional();
        }

    }

//    public function processClass($className)
//    {
//        $strategy = $this->introspectionStrategy;
//        $sClass = $this->directoryScanner->getClass($className, true, true);
//
//        if (!$sClass->isInstantiable()) {
//            return;
//        }
//
//        // determine supertypes
//        $superTypes = array();
//        if (($parentClasses = $sClass->getParentClasses()) !== null) {
//            $superTypes = array_merge($superTypes, $parentClasses);
//        }
//        if (($interfaces = $sClass->getInterfaces())) {
//            $superTypes = array_merge($superTypes, $interfaces);
//        }
//
//        $className = $sClass->getName();
//        $this->classes[$className] = array(
//            'supertypes'       => $superTypes,
//            'instantiator'     => null,
//            'methods'          => array(),
//            'parameters'       => array()
//        );
//
//        $def = &$this->classes[$className];
//
//        if ($def['instantiator'] == null) {
//            if ($sClass->isInstantiable()) {
//                $def['instantiator'] = '__construct';
//            }
//        }
//
//        if ($sClass->hasMethod('__construct')) {
//            $mScanner = $sClass->getMethod('__construct');
//            if ($mScanner->isPublic() && $mScanner->getNumberOfParameters() > 0) {
//                $def['methods']['__construct'] = true;
//                $this->processParams($def, $sClass, $mScanner);
//            }
//        }
//
//        foreach ($sClass->getMethods(true) as $mScanner) {
//            if (!$mScanner->isPublic()) {
//                continue;
//            }
//
//            $methodName = $mScanner->getName();
//
//            if ($mScanner->getName() === '__construct') {
//                continue;
//            }
//
//            if ($strategy->getUseAnnotations() == true) {
//
//                $annotations = $mScanner->getAnnotations($strategy->getAnnotationManager());
//
//                if (($annotations instanceof AnnotationCollection)
//                    && $annotations->hasAnnotation('Zend\Di\Definition\Annotation\Inject')) {
//
//                    $def['methods'][$methodName] = true;
//                    $this->processParams($def, $sClass, $mScanner);
//                    continue;
//                }
//            }
//
//            $methodPatterns = $this->introspectionStrategy->getMethodNameInclusionPatterns();
//
//            // matches a method injection pattern?
//            foreach ($methodPatterns as $methodInjectorPattern) {
//                preg_match($methodInjectorPattern, $methodName, $matches);
//                if ($matches) {
//                    $def['methods'][$methodName] = false; // check ot see if this is required?
//                    $this->processParams($def, $sClass, $mScanner);
//                    continue 2;
//                }
//            }
//
//        }
//
//        $interfaceInjectorPatterns = $this->introspectionStrategy->getInterfaceInjectionInclusionPatterns();
//
//        // matches the interface injection pattern
//        /** @var $sInterface \Zend\Code\Scanner\ClassScanner */
//        foreach ($sClass->getInterfaces(true) as $sInterface) {
//            foreach ($interfaceInjectorPatterns as $interfaceInjectorPattern) {
//                preg_match($interfaceInjectorPattern, $sInterface->getName(), $matches);
//                if ($matches) {
//                    foreach ($sInterface->getMethods(true) as $sMethod) {
//                        if ($sMethod->getName() === '__construct') {
                              // constructor not allowed in interfaces
//                            continue;
//                        }
//                        $def['methods'][$sMethod->getName()] = true;
//                        $this->processParams($def, $sClass, $sMethod);
//                    }
//                    continue 2;
//                }
//            }
//        }
//
//    }
//
//    protected function processParams(&$def, DerivedClassScanner $sClass, MethodScanner $sMethod)
//    {
//        if (count($sMethod->getParameters()) === 0) {
//            return;
//        }
//
//        $methodName = $sMethod->getName();
//
//        $def['parameters'][$methodName] = array();
//
//        foreach ($sMethod->getParameters(true) as $position => $p) {
//
//            /** @var $p \Zend\Code\Scanner\ParameterScanner  */
//            $actualParamName = $p->getName();
//
//            $paramName = $this->createDistinctParameterName($actualParamName, $sClass->getName());
//
//            $fqName = $sClass->getName() . '::' . $sMethod->getName() . ':' . $position;
//
//            $def['parameters'][$methodName][$fqName] = array();
//
//            // set the class name, if it exists
//            $def['parameters'][$methodName][$fqName][] = $actualParamName;
//            $def['parameters'][$methodName][$fqName][] = ($p->getClass() !== null) ? $p->getClass() : null;
//            $def['parameters'][$methodName][$fqName][] = !$p->isOptional();
//        }
//    }
//
//    protected function createDistinctParameterName($paramName, $class)
//    {
//        $currentParams = array();
//        if ($this->classes[$class]['parameters'] === array()) {
//            return $paramName;
//        }
//        foreach ($this->classes as $cdata) {
//            foreach ($cdata['parameters'] as $mdata) {
//                $currentParams = array_merge($currentParams, array_keys($mdata));
//            }
//        }
//
//        if (!in_array($paramName, $currentParams)) {
//            return $paramName;
//        }
//
//        $alt = 2;
//        while (in_array($paramName . (string) $alt, $currentParams)) {
//            $alt++;
//        }
//
//        return $paramName . (string) $alt;
//    }

    /**
     * {@inheritDoc}
     */
    public function getClasses()
    {
        return array_keys($this->classes);
    }

    /**
     * {@inheritDoc}
     */
    public function hasClass($class)
    {
        return (array_key_exists($class, $this->classes));
    }

    /**
     * {@inheritDoc}
     */
    public function getClassSupertypes($class)
    {
        if (!array_key_exists($class, $this->classes)) {
            $this->processClass($class);
        }

        return $this->classes[$class]['supertypes'];
    }

    /**
     * {@inheritDoc}
     */
    public function getInstantiator($class)
    {
        if (!array_key_exists($class, $this->classes)) {
            $this->processClass($class);
        }

        return $this->classes[$class]['instantiator'];
    }

    /**
     * {@inheritDoc}
     */
    public function hasMethods($class)
    {
        if (!array_key_exists($class, $this->classes)) {
            $this->processClass($class);
        }

        return (count($this->classes[$class]['methods']) > 0);
    }

    /**
     * {@inheritDoc}
     */
    public function hasMethod($class, $method)
    {
        if (!array_key_exists($class, $this->classes)) {
            $this->processClass($class);
        }

        return isset($this->classes[$class]['methods'][$method]);
    }

    /**
     * {@inheritDoc}
     */
    public function getMethods($class)
    {
        if (!array_key_exists($class, $this->classes)) {
            $this->processClass($class);
        }

        return $this->classes[$class]['methods'];
    }

    /**
     * {@inheritDoc}
     */
    public function hasMethodParameters($class, $method)
    {
        if (!isset($this->classes[$class])) {
            return false;
        }

        return (array_key_exists($method, $this->classes[$class]));
    }

    /**
     * {@inheritDoc}
     */
    public function getMethodParameters($class, $method)
    {
        if (!is_array($this->classes[$class])) {
            $this->processClass($class);
        }

        return $this->classes[$class]['parameters'][$method];
    }
}
