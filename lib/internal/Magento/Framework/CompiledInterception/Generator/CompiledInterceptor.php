<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\CompiledInterception\Generator;

use Magento\Framework\Code\Generator\CodeGeneratorInterface;
use Magento\Framework\Code\Generator\DefinedClasses;
use Magento\Framework\Code\Generator\EntityAbstract;
use Magento\Framework\Code\Generator\Io;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Interception\Code\Generator\InterceptorInterface;
use Magento\Framework\Interception\DefinitionInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Compiled interceptors generator, please see ../README.md for details
 */
class CompiledInterceptor extends EntityAbstract implements InterceptorInterface
{
    public const ENTITY_TYPE = 'interceptor';

    /**
     * @var array
     */
    private $classMethods;

    /**
     * @var array
     */
    private $classProperties;

    /**
     * @var \ReflectionClass
     */
    private $baseReflection;

    /**
     * @var AreasPluginList
     */
    private $areasPlugins;

    /**
     * CompiledInterceptor constructor.
     * @param AreasPluginList $areasPlugins
     * @param string|null $sourceClassName
     * @param string|null $resultClassName
     * @param Io|null $ioObject
     * @param CodeGeneratorInterface|null $classGenerator
     * @param DefinedClasses|null $definedClasses
     */
    public function __construct(
        AreasPluginList $areasPlugins,
        $sourceClassName = null,
        $resultClassName = null,
        Io $ioObject = null,
        CodeGeneratorInterface $classGenerator = null,
        DefinedClasses $definedClasses = null
    ) {
        parent::__construct(
            $sourceClassName,
            $resultClassName,
            $ioObject,
            $classGenerator,
            $definedClasses
        );
        $this->areasPlugins = $areasPlugins;
    }

    /**
     * Unused function required by production mode interface
     *
     * @param mixed $interceptedMethods
     */
    public function setInterceptedMethods($interceptedMethods)
    {
        // this is not used as methods are read from reflection
        $this->interceptedMethods = $interceptedMethods;
    }

    /**
     * Get properties to be set in constructor.
     *
     * @return array
     */
    public static function propertiesToSetInConstructor()
    {
        return [
            ScopeInterface::class => '____scope',
            ObjectManagerInterface::class => '____om',
        ];
    }

    /**
     * Get all class methods
     *
     * @return array|null
     * @throws \ReflectionException
     */
    protected function _getClassMethods()
    {
        $this->generateMethodsAndProperties();
        return $this->classMethods;
    }

    /**
     * Get all class properties
     *
     * @return array|null
     * @throws \ReflectionException
     */
    protected function _getClassProperties()
    {
        $this->generateMethodsAndProperties();
        return $this->classProperties;
    }

    /**
     * Get default constructor definition for generated class
     *
     * @return array
     * @throws \ReflectionException
     */
    protected function _getDefaultConstructorDefinition()
    {
        return $this->injectPropertiesSettersToConstructor(
            $this->getSourceClassReflection()->getConstructor(),
            static::propertiesToSetInConstructor()
        );
    }

    /**
     * Generate class source
     *
     * @return bool|string
     * @throws \ReflectionException
     */
    protected function _generateCode()
    {
        if ($this->getSourceClassReflection()->isInterface()) {
            return false;
        } else {
            $this->_classGenerator->setExtendedClass($this->getSourceClassName());
        }
        $this->generateMethodsAndProperties();
        return parent::_generateCode();
    }

    /**
     * Generate all methods and properties
     *
     * @throws \ReflectionException
     */
    private function generateMethodsAndProperties()
    {
        if ($this->classMethods === null) {
            $this->classMethods = [];
            $this->classProperties = [];

            foreach (static::propertiesToSetInConstructor() as $type => $name) {
                $this->_classGenerator->addUse($type);
                $this->classProperties[] = [
                    'name' => $name,
                    'visibility' => 'private',
                    'docblock' => [
                        'tags' => [['name' => 'var', 'description' => substr(strrchr($type, "\\"), 1)]],
                    ]
                ];
            }
            $this->classMethods[] = $this->_getDefaultConstructorDefinition();
            $this->overrideMethodsAndGeneratePluginGetters($this->getSourceClassReflection());
        }
    }

    /**
     * Get reflection of source class
     *
     * @return \ReflectionClass
     * @throws \ReflectionException
     */
    private function getSourceClassReflection()
    {
        if ($this->baseReflection === null) {
            $this->baseReflection = new \ReflectionClass($this->getSourceClassName());
        }
        return $this->baseReflection;
    }

    /**
     * Whether method is intercepted
     *
     * @param \ReflectionMethod $method
     * @return bool
     */
    private function isInterceptedMethod(\ReflectionMethod $method)
    {
        return !($method->isConstructor() || $method->isFinal() || $method->isStatic() || $method->isDestructor()) &&
            !in_array($method->getName(), ['__sleep', '__wakeup', '__clone']);
    }

    /**
     * Generate compiled methods and plugin getters
     *
     * @param \ReflectionClass $reflection
     */
    private function overrideMethodsAndGeneratePluginGetters(\ReflectionClass $reflection)
    {
        $publicMethods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        $allPlugins = [];
        foreach ($publicMethods as $method) {
            if ($this->isInterceptedMethod($method)) {
                $config = $this->getPluginsConfig($method, $allPlugins);
                if (!empty($config)) {
                    $this->classMethods[] = $this->getCompiledMethodInfo($method, $config);
                }
            }
        }
        foreach ($allPlugins as $plugins) {
            foreach ($plugins as $plugin) {
                $this->classMethods[] = $this->getPluginGetterInfo($plugin);
            }
        }
    }

    /**
     * Generate class constructor adding required properties when types are not present in parent constructor
     *
     * @param \ReflectionMethod|null $parentConstructor
     * @param array $properties
     * @return array
     */
    private function injectPropertiesSettersToConstructor(\ReflectionMethod $parentConstructor = null, $properties = [])
    {
        if ($parentConstructor == null) {
            $parameters = [];
            $body = [];
        } else {
            $parameters = $parentConstructor->getParameters();
            $parentCallParams = [];
            foreach ($parameters as $parameter) {
                $parentCallParams[] = '$' . $parameter->getName();
            }
            $body = ["parent::__construct(" . implode(', ', $parentCallParams) . ");"];
        }
        $extraSetters = array_fill_keys(array_values($properties), null);
        foreach ($parameters as $parameter) {
            if ($parameter->getType()) {
                $type = $parameter->getType()->getName();
                if (isset($properties[$type])) {
                    $extraSetters[$properties[$type]] = $parameter->getName();
                }
            }
        }
        $parameters = array_map([$this, '_getMethodParameterInfo'], $parameters);
        foreach ($extraSetters as $name => $paramName) {
            $class = array_search($name, $properties);
            if ($paramName !== null) {
                array_unshift(
                    $body,
                    "\$this->$name = \$$paramName;"
                );
            } elseif ($class === ObjectManagerInterface::class) {
                array_unshift(
                    $body,
                    "\$this->$name = \Magento\Framework\App\ObjectManager::getInstance();"
                );
            } else {
                array_unshift(
                    $body,
                    "\$this->$name = \Magento\Framework\App\ObjectManager::getInstance()->get(\\$class::class);"
                );
            }
        }
        return [
            'name' => '__construct',
            'parameters' => $parameters,
            'body' => implode("\n", $body),
            'docblock' => ['shortDescription' => '@inheritdoc'],
        ];
    }

    /**
     * Adds tabulation to nested code block
     *
     * @param array $body
     * @param array $sub
     * @param int $indent
     */
    private function addCodeSubBlock(&$body, $sub, $indent = 1)
    {
        foreach ($sub as $line) {
            $body[] = ('' === $line) ? '' : str_repeat("\t", $indent) . $line;
        }
    }

    /**
     * Generate source of before plugins
     *
     * @param array $plugins
     * @param string $methodName
     * @param string $extraParams
     * @param string $parametersList
     * @return array
     */
    private function compileBeforePlugins($plugins, $methodName, $extraParams, $parametersList)
    {
        $lines = [];
        foreach ($plugins as $plugin) {
            $call = "\$this->" . $this->getGetterName($plugin) . "()->$methodName(\$this$extraParams);";

            if (!empty($parametersList)) {
                $lines[] = "\$beforeResult = " . $call;
                $lines[] = "if (\$beforeResult !== null) list({$parametersList}) = (array)\$beforeResult;";
            } else {
                $lines[] = $call;
            }
            $lines[] = "";
        }
        return $lines;
    }

    /**
     * Generate source of around plugin
     *
     * @param string $methodName
     * @param array $plugin
     * @param string $capitalizedName
     * @param string $extraParams
     * @param array $parameters
     * @param bool $returnVoid
     * @return array
     */
    private function compileAroundPlugin($methodName, $plugin, $capitalizedName, $extraParams, $parameters, $returnVoid)
    {
        $lines = [];
        $lines[] = "\$this->{$this->getGetterName($plugin)}()->around$capitalizedName" .
            "(\$this, function({$this->getParameterListForNextCallback($parameters)}){";
        $this->addCodeSubBlock(
            $lines,
            $this->getMethodSourceFromConfig($methodName, $plugin['next'] ?: [], $parameters, $returnVoid)
        );
        $lines[] = "}$extraParams);";
        return $lines;
    }

    /**
     * Generate source of after plugins
     *
     * @param array $plugins
     * @param string $methodName
     * @param string $extraParams
     * @param bool $returnVoid
     * @return array
     */
    private function compileAfterPlugins($plugins, $methodName, $extraParams, $returnVoid)
    {
        $lines = [];
        foreach ($plugins as $plugin) {
            $call = "\$this->" . $this->getGetterName($plugin) . "()->$methodName(\$this, ";

            if (!$returnVoid) {
                $lines[] = ["$call\$result$extraParams);"];
            } else {
                $lines[] = ["{$call}null$extraParams);"];
            }
        }
        return $lines;
    }

    /**
     * Generate interceptor source using config
     *
     * @param string $methodName
     * @param array $conf
     * @param array $parameters
     * @param bool $returnVoid
     * @return array
     */
    private function getMethodSourceFromConfig($methodName, $conf, $parameters, $returnVoid)
    {
        $capitalizedName = ucfirst($methodName);
        $parametersList = $this->getParameterList($parameters);
        $extraParams = empty($parameters) ? '' : (', ' . $parametersList);

        if (isset($conf[DefinitionInterface::LISTENER_BEFORE])) {
            $body = $this->compileBeforePlugins(
                $conf[DefinitionInterface::LISTENER_BEFORE],
                'before' . $capitalizedName,
                $extraParams,
                $parametersList
            );
        } else {
            $body = [];
        }

        $resultChain = [];
        if (isset($conf[DefinitionInterface::LISTENER_AROUND])) {
            $resultChain[] = $this->compileAroundPlugin(
                $methodName,
                $conf[DefinitionInterface::LISTENER_AROUND],
                $capitalizedName,
                $extraParams,
                $parameters,
                $returnVoid
            );
        } else {
            $resultChain[] = ["parent::{$methodName}({$this->getParameterList($parameters)});"];
        }

        if (isset($conf[DefinitionInterface::LISTENER_AFTER])) {
            $resultChain = array_merge(
                $resultChain,
                $this->compileAfterPlugins(
                    $conf[DefinitionInterface::LISTENER_AFTER],
                    'after' . $capitalizedName,
                    $extraParams,
                    $returnVoid
                )
            );
        }
        return array_merge($body, $this->getResultChainLines($resultChain, $returnVoid));
    }

    /**
     * Implode result chain into list of assignments
     *
     * @param array $resultChain
     * @param bool $returnVoid
     * @return array
     */
    private function getResultChainLines($resultChain, $returnVoid)
    {
        $lines = [];
        $first = true;
        foreach ($resultChain as $lp => $piece) {
            if ($first) {
                $first = false;
            } else {
                $lines[] = "";
            }
            if (!$returnVoid) {
                $piece[0] = (($lp + 1 == count($resultChain)) ? "return " : "\$result = ") . $piece[0];
            }
            foreach ($piece as $line) {
                $lines[] = $line;
            }
        }
        return $lines;
    }

    /**
     * Get parameters definition for next callback
     *
     * @param array $parameters
     * @return string
     */
    private function getParameterListForNextCallback(array $parameters)
    {
        $ret = [];
        foreach ($parameters as $parameter) {
            $ret [] =
                ($parameter->isPassedByReference() ? '&' : '') .
                "\${$parameter->getName()}" .
                ($parameter->isDefaultValueAvailable() ?
                    ' = ' . ($parameter->isDefaultValueConstant() ?
                        $parameter->getDefaultValueConstantName() :
                        str_replace("\n", '', var_export($parameter->getDefaultValue(), true))) :
                    '');
        }
        return implode(', ', $ret);
    }

    /**
     * Implodes parameters into list for call
     *
     * @param array(\ReflectionParameter) $parameters
     * @return string
     */
    private function getParameterList(array $parameters)
    {
        $ret = [];
        foreach ($parameters as $parameter) {
            $ret [] = "\${$parameter->getName()}";
        }
        return implode(', ', $ret);
    }

    /**
     * Get plugin getter name
     *
     * @param array $plugin
     * @return string
     */
    private function getGetterName($plugin)
    {
        return '____plugin_' . $plugin['clean_name'];
    }

    /**
     * Prepares plugin getter for code generator
     *
     * @param array $plugin
     * @return array
     */
    private function getPluginGetterInfo($plugin)
    {
        return [
            'name' => $this->getGetterName($plugin),
            'visibility' => 'private',
            'parameters' => [],
            'body' => "return \$this->____om->get(\\" . "{$plugin['class']}::class);",
            'docblock' => [
                'shortDescription' => 'plugin "' . $plugin['code'] . '"' . "\n" . '@return \\' . $plugin['class']
            ],
        ];
    }

    /**
     * Get compiled method data for code generator
     *
     * @param \ReflectionMethod $method
     * @param array $config
     * @return array
     */
    private function getCompiledMethodInfo(\ReflectionMethod $method, $config)
    {
        $parameters = $method->getParameters();
        $returnsVoid = ($method->hasReturnType() && $method->getReturnType()->getName() == 'void');

        $cases = $this->getScopeCasesFromConfig($config);

        if (count($cases) == 1) {
            $body = $this->getMethodSourceFromConfig($method->getName(), $cases[0]['conf'], $parameters, $returnsVoid);
        } else {
            $body = [
                'switch ($this->____scope->getCurrentScope()) {'
            ];

            foreach ($cases as $case) {
                // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                $body = array_merge($body, $case['cases']);
                $this->addCodeSubBlock(
                    $body,
                    $this->getMethodSourceFromConfig($method->getName(), $case['conf'], $parameters, $returnsVoid),
                    2
                );
                if ($returnsVoid) {
                    $body[] = "\t\tbreak;";
                }
            }

            $body[] = "}";
        }

        $returnType = $method->getReturnType();
        $returnTypeValue = $returnType
            ? ($returnType->allowsNull() ? '?' : '') . $returnType->getName()
            : null;
        if ($returnTypeValue === 'self') {
            $returnTypeValue = $this->_getFullyQualifiedClassName($method->getDeclaringClass()->getName());
        }
        return [
            'name' => ($method->returnsReference() ? '& ' : '') . $method->getName(),
            'parameters' =>array_map([$this, '_getMethodParameterInfo'], $parameters),
            'body' => implode("\n", $body),
            'returnType' => $returnTypeValue,
            'docblock' => ['shortDescription' => '@inheritdoc'],
        ];
    }

    /**
     * Get scope cases from config
     *
     * @param array $config
     * @return array
     */
    private function getScopeCasesFromConfig($config)
    {
        $cases = [];
        //group cases by config
        foreach ($config as $scope => $conf) {
            $caseStr = "\tcase '$scope':";
            foreach ($cases as &$case) {
                if ($case['conf'] == $conf) {
                    $case['cases'][] = $caseStr;
                    continue 2;
                }
            }
            $cases[] = ['cases'=>[$caseStr], 'conf'=>$conf];
        }
        $cases[count($cases) - 1]['cases'] = ["\tdefault:"];
        return $cases;
    }

    /**
     * Generate array with plugin info
     *
     * @param CompiledPluginList $plugins
     * @param string $code
     * @param string $className
     * @param array $allPlugins
     * @param string|null $next
     * @return mixed
     */
    private function getPluginInfo(CompiledPluginList $plugins, $code, $className, &$allPlugins, $next = null)
    {
        $className = $plugins->getPluginType($className, $code);
        if (!isset($allPlugins[$code])) {
            $allPlugins[$code] = [];
        }
        if (empty($allPlugins[$code][$className])) {
            $suffix = count($allPlugins[$code]) ? count($allPlugins[$code]) + 1 : '';
            $allPlugins[$code][$className] = [
                'code' => $code,
                'class' => $className,
                'clean_name' => preg_replace("/[^A-Za-z0-9_]/", '_', $code . $suffix)
            ];
        }
        $result = $allPlugins[$code][$className];
        $result['next'] = $next;
        return $result;
    }

    /**
     * Get next set of plugins
     *
     * @param CompiledPluginList $plugins
     * @param string $className
     * @param string $method
     * @param array $allPlugins
     * @param string $next
     * @return array
     */
    private function getPluginsChain(CompiledPluginList $plugins, $className, $method, &$allPlugins, $next = '__self')
    {
        $result = $plugins->getNext($className, $method, $next);
        if (!empty($result[DefinitionInterface::LISTENER_BEFORE])) {
            foreach ($result[DefinitionInterface::LISTENER_BEFORE] as $k => $code) {
                $result[DefinitionInterface::LISTENER_BEFORE][$k] = $this->getPluginInfo(
                    $plugins,
                    $code,
                    $className,
                    $allPlugins
                );
            }
        }
        if (!empty($result[DefinitionInterface::LISTENER_AFTER])) {
            foreach ($result[DefinitionInterface::LISTENER_AFTER] as $k => $code) {
                $result[DefinitionInterface::LISTENER_AFTER][$k] = $this->getPluginInfo(
                    $plugins,
                    $code,
                    $className,
                    $allPlugins
                );
            }
        }
        if (isset($result[DefinitionInterface::LISTENER_AROUND])) {
            $result[DefinitionInterface::LISTENER_AROUND] = $this->getPluginInfo(
                $plugins,
                $result[DefinitionInterface::LISTENER_AROUND],
                $className,
                $allPlugins,
                $this->getPluginsChain(
                    $plugins,
                    $className,
                    $method,
                    $allPlugins,
                    $result[DefinitionInterface::LISTENER_AROUND]
                )
            );
        }
        return $result;
    }

    /**
     * Generates recursive maps of plugins for given method
     *
     * @param \ReflectionMethod $method
     * @param array $allPlugins
     * @return array
     */
    private function getPluginsConfig(\ReflectionMethod $method, &$allPlugins)
    {
        $className = ltrim($this->getSourceClassName(), '\\');

        $result = [];
        foreach ($this->areasPlugins->getPluginsConfigForAllAreas() as $scope => $pluginsList) {
            $pluginChain = $this->getPluginsChain($pluginsList, $className, $method->getName(), $allPlugins);
            if ($pluginChain) {
                $result[$scope] = $pluginChain;
            }
        }
        //if plugins are not empty make sure default case will be handled
        if (!empty($result) && !$pluginChain) {
            $result[$scope] = [];
        }
        return $result;
    }
}
