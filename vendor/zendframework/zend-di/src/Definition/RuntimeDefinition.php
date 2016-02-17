<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Di\Definition;

use Zend\Code\Annotation\AnnotationCollection;
use Zend\Code\Reflection;
use Zend\Di\Di;

/**
 * Class definitions based on runtime reflection
 */
class RuntimeDefinition implements DefinitionInterface
{
    /**
     * @var array
     */
    protected $classes = array();

    /**
     * @var bool
     */
    protected $explicitLookups = false;

    /**
     * @var IntrospectionStrategy
     */
    protected $introspectionStrategy = null;

    /**
     * @var array
     */
    protected $injectionMethods = array();

    /**
     * @var array
     */
    protected $processedClass = array();

    /**
     * Constructor
     *
     * @param null|IntrospectionStrategy $introspectionStrategy
     * @param array|null                 $explicitClasses
     */
    public function __construct(IntrospectionStrategy $introspectionStrategy = null, array $explicitClasses = null)
    {
        $this->introspectionStrategy = ($introspectionStrategy) ?: new IntrospectionStrategy();
        if ($explicitClasses) {
            $this->setExplicitClasses($explicitClasses);
        }
    }

    /**
     * @param  IntrospectionStrategy $introspectionStrategy
     * @return void
     */
    public function setIntrospectionStrategy(IntrospectionStrategy $introspectionStrategy)
    {
        $this->introspectionStrategy = $introspectionStrategy;
    }

    /**
     * @return IntrospectionStrategy
     */
    public function getIntrospectionStrategy()
    {
        return $this->introspectionStrategy;
    }

    /**
     * Set explicit classes
     *
     * @param array $explicitClasses
     */
    public function setExplicitClasses(array $explicitClasses)
    {
        $this->explicitLookups = true;
        foreach ($explicitClasses as $eClass) {
            $this->classes[$eClass] = true;
        }
        $this->classes = $explicitClasses;
    }

    /**
     * @param string $class
     */
    public function forceLoadClass($class)
    {
        $this->processClass($class, true);
    }

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
        if ($this->explicitLookups === true) {
            return (array_key_exists($class, $this->classes));
        }

        return class_exists($class) || interface_exists($class);
    }

    /**
     * {@inheritDoc}
     */
    public function getClassSupertypes($class)
    {
        $this->processClass($class);
        return $this->classes[$class]['supertypes'];
    }

    /**
     * {@inheritDoc}
     */
    public function getInstantiator($class)
    {
        $this->processClass($class);
        return $this->classes[$class]['instantiator'];
    }

    /**
     * {@inheritDoc}
     */
    public function hasMethods($class)
    {
        $this->processClass($class);
        return (count($this->classes[$class]['methods']) > 0);
    }

    /**
     * {@inheritDoc}
     */
    public function hasMethod($class, $method)
    {
        $this->processClass($class);
        return isset($this->classes[$class]['methods'][$method]);
    }

    /**
     * {@inheritDoc}
     */
    public function getMethods($class)
    {
        $this->processClass($class);
        return $this->classes[$class]['methods'];
    }

    /**
     * {@inheritDoc}
     */
    public function hasMethodParameters($class, $method)
    {
        $this->processClass($class);
        return (array_key_exists($method, $this->classes[$class]['parameters']));
    }

    /**
     * {@inheritDoc}
     */
    public function getMethodParameters($class, $method)
    {
        $this->processClass($class);
        return $this->classes[$class]['parameters'][$method];
    }

    /**
     * @param string $class
     * @param bool $forceLoad
     */
    protected function processClass($class, $forceLoad = false)
    {
        if (!isset($this->processedClass[$class]) || $this->processedClass[$class] === false) {
            $this->processedClass[$class] = (array_key_exists($class, $this->classes) && is_array($this->classes[$class]));
        }

        if (!$forceLoad && $this->processedClass[$class]) {
            return;
        }

        $strategy = $this->introspectionStrategy; // localize for readability

        /** @var $rClass \Zend\Code\Reflection\ClassReflection */
        $rClass = new Reflection\ClassReflection($class);
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
                && $annotations->hasAnnotation('Zend\Di\Definition\Annotation\Instantiator')) {
                // @todo Instantiator support in annotations
            }
        }

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

        $def['supertypes'] = array_keys(array_flip($supertypes));

        if ($def['instantiator'] === null) {
            if ($rClass->isInstantiable()) {
                $def['instantiator'] = '__construct';
            }
        }

        if ($rClass->hasMethod('__construct')) {
            $def['methods']['__construct'] = Di::METHOD_IS_CONSTRUCTOR; // required
            $this->processParams($def, $rClass, $rClass->getMethod('__construct'));
        }

        foreach ($rClass->getMethods(Reflection\MethodReflection::IS_PUBLIC) as $rMethod) {
            $methodName = $rMethod->getName();

            if ($rMethod->getName() === '__construct' || $rMethod->isStatic()) {
                continue;
            }

            if ($strategy->getUseAnnotations() == true) {
                $annotations = $rMethod->getAnnotations($strategy->getAnnotationManager());

                if (($annotations instanceof AnnotationCollection)
                    && $annotations->hasAnnotation('Zend\Di\Definition\Annotation\Inject')) {
                    // use '@inject' and search for parameters
                    $def['methods'][$methodName] = Di::METHOD_IS_EAGER;
                    $this->processParams($def, $rClass, $rMethod);
                    continue;
                }
            }

            $methodPatterns = $this->introspectionStrategy->getMethodNameInclusionPatterns();

            // matches a method injection pattern?
            foreach ($methodPatterns as $methodInjectorPattern) {
                preg_match($methodInjectorPattern, $methodName, $matches);
                if ($matches) {
                    $def['methods'][$methodName] = Di::METHOD_IS_OPTIONAL; // check ot see if this is required?
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
                        if (($rMethod->getName() === '__construct') || !count($rMethod->getParameters())) {
                            // constructor not allowed in interfaces
                            // Don't call interface methods without a parameter (Some aware interfaces define setters in ZF2)
                            continue;
                        }
                        $def['methods'][$rMethod->getName()] = Di::METHOD_IS_AWARE;
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
            $def['parameters'][$methodName][$fqName][] = !($optional = $p->isOptional() && $p->isDefaultValueAvailable());
            $def['parameters'][$methodName][$fqName][] = $optional ? $p->getDefaultValue() : null;
        }
    }
}
