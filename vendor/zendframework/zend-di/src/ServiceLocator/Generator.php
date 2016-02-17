<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Di\ServiceLocator;

use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\FileGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Di\Di;
use Zend\Di\Exception;

/**
 * Generator that creates the body of a service locator that can emulate the logic of the given Zend\Di\Di instance
 * without class definitions
 */
class Generator
{
    protected $containerClass = 'ApplicationContext';

    /** @var DependencyInjectorProxy */
    protected $injector;

    /**
     * @var null|string
     */
    protected $namespace;

    /**
     * Constructor
     *
     * Requires a DependencyInjection manager on which to operate.
     *
     * @param Di $injector
     */
    public function __construct(Di $injector)
    {
        $this->injector = new DependencyInjectorProxy($injector);
    }

    /**
     * Set the class name for the generated service locator container
     *
     * @param  string    $name
     * @return Generator
     */
    public function setContainerClass($name)
    {
        $this->containerClass = $name;

        return $this;
    }

    /**
     * Set the namespace to use for the generated class file
     *
     * @param  string    $namespace
     * @return Generator
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * Construct, configure, and return a PHP class file code generation object
     *
     * Creates a Zend\Code\Generator\FileGenerator object that has
     * created the specified class and service locator methods.
     *
     * @param  null|string                         $filename
     * @throws \Zend\Di\Exception\RuntimeException
     * @return FileGenerator
     */
    public function getCodeGenerator($filename = null)
    {
        $injector       = $this->injector;
        $im             = $injector->instanceManager();
        $indent         = '    ';
        $aliases        = $this->reduceAliases($im->getAliases());
        $caseStatements = array();
        $getters        = array();
        $definitions    = $injector->definitions();

        $fetched = array_unique(array_merge($definitions->getClasses(), $im->getAliases()));

        foreach ($fetched as $name) {
            $getter = $this->normalizeAlias($name);
            $meta   = $injector->get($name);
            $params = $meta->getParams();

            // Build parameter list for instantiation
            foreach ($params as $key => $param) {
                if (null === $param || is_scalar($param) || is_array($param)) {
                    $string = var_export($param, 1);
                    if (strstr($string, '::__set_state(')) {
                        throw new Exception\RuntimeException('Arguments in definitions may not contain objects');
                    }
                    $params[$key] = $string;
                } elseif ($param instanceof GeneratorInstance) {
                    /* @var $param GeneratorInstance */
                    $params[$key] = sprintf('$this->%s()', $this->normalizeAlias($param->getName()));
                } else {
                    $message = sprintf('Unable to use object arguments when building containers. Encountered with "%s", parameter of type "%s"', $name, get_class($param));
                    throw new Exception\RuntimeException($message);
                }
            }

            // Strip null arguments from the end of the params list
            $reverseParams = array_reverse($params, true);
            foreach ($reverseParams as $key => $param) {
                if ('NULL' === $param) {
                    unset($params[$key]);
                    continue;
                }
                break;
            }

            // Create instantiation code
            $constructor = $meta->getConstructor();
            if ('__construct' != $constructor) {
                // Constructor callback
                $callback = var_export($constructor, 1);
                if (strstr($callback, '::__set_state(')) {
                    throw new Exception\RuntimeException('Unable to build containers that use callbacks requiring object instances');
                }
                if (count($params)) {
                    $creation = sprintf('$object = call_user_func(%s, %s);', $callback, implode(', ', $params));
                } else {
                    $creation = sprintf('$object = call_user_func(%s);', $callback);
                }
            } else {
                // Normal instantiation
                $className = '\\' . ltrim($name, '\\');
                $creation = sprintf('$object = new %s(%s);', $className, implode(', ', $params));
            }

            // Create method call code
            $methods = '';
            foreach ($meta->getMethods() as $methodData) {
                if (!isset($methodData['name']) && !isset($methodData['method'])) {
                    continue;
                }
                $methodName   = isset($methodData['name']) ? $methodData['name'] : $methodData['method'];
                $methodParams = $methodData['params'];

                // Create method parameter representation
                foreach ($methodParams as $key => $param) {
                    if (null === $param || is_scalar($param) || is_array($param)) {
                        $string = var_export($param, 1);
                        if (strstr($string, '::__set_state(')) {
                            throw new Exception\RuntimeException('Arguments in definitions may not contain objects');
                        }
                        $methodParams[$key] = $string;
                    } elseif ($param instanceof GeneratorInstance) {
                        $methodParams[$key] = sprintf('$this->%s()', $this->normalizeAlias($param->getName()));
                    } else {
                        $message = sprintf('Unable to use object arguments when generating method calls. Encountered with class "%s", method "%s", parameter of type "%s"', $name, $methodName, get_class($param));
                        throw new Exception\RuntimeException($message);
                    }
                }

                // Strip null arguments from the end of the params list
                $reverseParams = array_reverse($methodParams, true);
                foreach ($reverseParams as $key => $param) {
                    if ('NULL' === $param) {
                        unset($methodParams[$key]);
                        continue;
                    }
                    break;
                }

                $methods .= sprintf("\$object->%s(%s);\n", $methodName, implode(', ', $methodParams));
            }

            // Generate caching statement
            $storage = '';
            if ($im->hasSharedInstance($name, $params)) {
                $storage = sprintf("\$this->services['%s'] = \$object;\n", $name);
            }

            // Start creating getter
            $getterBody = '';

            // Create fetch of stored service
            if ($im->hasSharedInstance($name, $params)) {
                $getterBody .= sprintf("if (isset(\$this->services['%s'])) {\n", $name);
                $getterBody .= sprintf("%sreturn \$this->services['%s'];\n}\n\n", $indent, $name);
            }

            // Creation and method calls
            $getterBody .= sprintf("%s\n", $creation);
            $getterBody .= $methods;

            // Stored service
            $getterBody .= $storage;

            // End getter body
            $getterBody .= "return \$object;\n";

            $getterDef = new MethodGenerator();
            $getterDef->setName($getter);
            $getterDef->setBody($getterBody);
            $getters[] = $getterDef;

            // Get cases for case statements
            $cases = array($name);
            if (isset($aliases[$name])) {
                $cases = array_merge($aliases[$name], $cases);
            }

            // Build case statement and store
            $statement = '';
            foreach ($cases as $value) {
                $statement .= sprintf("%scase '%s':\n", $indent, $value);
            }
            $statement .= sprintf("%sreturn \$this->%s();\n", str_repeat($indent, 2), $getter);

            $caseStatements[] = $statement;
        }

        // Build switch statement
        $switch  = sprintf("switch (%s) {\n%s\n", '$name', implode("\n", $caseStatements));
        $switch .= sprintf("%sdefault:\n%sreturn parent::get(%s, %s);\n", $indent, str_repeat($indent, 2), '$name', '$params');
        $switch .= "}\n\n";

        // Build get() method
        $nameParam   = new ParameterGenerator();
        $nameParam->setName('name');
        $paramsParam = new ParameterGenerator();
        $paramsParam->setName('params')
                    ->setType('array')
                    ->setDefaultValue(array());

        $get = new MethodGenerator();
        $get->setName('get');
        $get->setParameters(array(
            $nameParam,
            $paramsParam,
        ));
        $get->setBody($switch);

        // Create getters for aliases
        $aliasMethods = array();
        foreach ($aliases as $class => $classAliases) {
            foreach ($classAliases as $alias) {
                $aliasMethods[] = $this->getCodeGenMethodFromAlias($alias, $class);
            }
        }

        // Create class code generation object
        $container = new ClassGenerator();
        $container->setName($this->containerClass)
                  ->setExtendedClass('ServiceLocator')
                  ->addMethodFromGenerator($get)
                  ->addMethods($getters)
                  ->addMethods($aliasMethods);

        // Create PHP file code generation object
        $classFile = new FileGenerator();
        $classFile->setUse('Zend\Di\ServiceLocator')
                  ->setClass($container);

        if (null !== $this->namespace) {
            $classFile->setNamespace($this->namespace);
        }

        if (null !== $filename) {
            $classFile->setFilename($filename);
        }

        return $classFile;
    }

    /**
     * Reduces aliases
     *
     * Takes alias list and reduces it to a 2-dimensional array of
     * class names pointing to an array of aliases that resolve to
     * it.
     *
     * @param  array $aliasList
     * @return array
     */
    protected function reduceAliases(array $aliasList)
    {
        $reduced = array();
        $aliases = array_keys($aliasList);
        foreach ($aliasList as $alias => $service) {
            if (in_array($service, $aliases)) {
                do {
                    $service = $aliasList[$service];
                } while (in_array($service, $aliases));
            }
            if (!isset($reduced[$service])) {
                $reduced[$service] = array();
            }
            $reduced[$service][] = $alias;
        }

        return $reduced;
    }

    /**
     * Create a PhpMethod code generation object named after a given alias
     *
     * @param  string          $alias
     * @param  string          $class Class to which alias refers
     * @return MethodGenerator
     */
    protected function getCodeGenMethodFromAlias($alias, $class)
    {
        $alias = $this->normalizeAlias($alias);
        $method = new MethodGenerator();
        $method->setName($alias);
        $method->setBody(sprintf('return $this->get(\'%s\');', $class));

        return $method;
    }

    /**
     * Normalize an alias to a getter method name
     *
     * @param  string $alias
     * @return string
     */
    protected function normalizeAlias($alias)
    {
        $normalized = preg_replace('/[^a-zA-Z0-9]/', ' ', $alias);
        $normalized = 'get' . str_replace(' ', '', ucwords($normalized));

        return $normalized;
    }
}
