<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\CompiledInterception\Generator;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Code\Generator\EntityAbstract;
use Magento\Framework\Config\Scope;
use Magento\Framework\Interception\Code\Generator\Interceptor;
use Magento\Framework\Interception\DefinitionInterface;

class CompiledInterceptor extends Interceptor
{

    protected $plugins;

    protected $classMethods = [];
    protected $classProperties = [];

    public function __construct(
        $sourceClassName = null,
        $resultClassName = null,
        \Magento\Framework\Code\Generator\Io $ioObject = null,
        \Magento\Framework\Code\Generator\CodeGeneratorInterface $classGenerator = null,
        \Magento\Framework\Code\Generator\DefinedClasses $definedClasses = null,
        $plugins = null
    )
    {
        parent::__construct($sourceClassName,
            $resultClassName ,
            $ioObject,
            $classGenerator,
            $definedClasses);

        if ($plugins !== null) {
            $this->plugins = $plugins;
        } else {
            $this->plugins = [];
            foreach (['primary', 'frontend', 'adminhtml', 'crontab', 'webapi_rest', 'webapi_soap'] as $scope) {
                $this->plugins[$scope] = new CompiledPluginList(ObjectManager::getInstance(), $scope);
            }
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setInterceptedMethods($interceptedMethods)
    {
        //NOOP
    }

    protected function _generateCode()
    {
        $typeName = $this->getSourceClassName();
        $reflection = new \ReflectionClass($typeName);

        if ($reflection->isInterface()) {
            return false;
        } else {
            $this->_classGenerator->setExtendedClass($typeName);
        }

        $this->classMethods = [];
        $this->classProperties = [];
        $this->injectPropertiesSettersToConstructor($reflection->getConstructor(), [
            Scope::class => '____scope',
            ObjectManagerInterface::class => '____om',
        ]);
        $this->overrideMethodsAndGeneratePluginGetters($reflection);

        //return parent::_generateCode();
        return EntityAbstract::_generateCode();
    }

    protected function overrideMethodsAndGeneratePluginGetters(\ReflectionClass $reflection)
    {
        $publicMethods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        $allPlugins = [];
        foreach ($publicMethods as $method) {
            if ($this->isInterceptedMethod($method)) {
                $config = $this->_getPluginsConfig($method, $allPlugins);
                if (!empty($config)) {
                    $this->classMethods[] = $this->_getCompiledMethodInfo($method, $config);
                }
            }
        }
        foreach ($allPlugins as $plugins) {
            foreach ($plugins as $plugin) {
                $this->classMethods[] = $this->_getPluginGetterInfo($plugin);
                $this->classProperties[] = $this->_getPluginPropertyInfo($plugin);
            }
        }
    }

    protected function injectPropertiesSettersToConstructor(\ReflectionMethod $parentConstructor = null, $properties = [])
    {
        if ($parentConstructor == null) {
            $parameters = [];
            $body = [];
        } else {
            $parameters = $parentConstructor->getParameters();
            foreach ($parameters as $parameter) {
                $parentCallParams[] = '$' . $parameter->getName();
            }
            $body = ["parent::__construct(" . implode(', ', $parentCallParams) .");"];
        }
        foreach ($properties as $type => $name) {
            $this->_classGenerator->addUse($type);
            $this->classProperties[] = [
                'name' => $name,
                'visibility' => 'private',
                'docblock' => [
                    'tags' => [['name' => 'var', 'description' => substr(strrchr($type, "\\"), 1)]],
                ]
            ];
        }
        $extraParams = $properties;
        $extraSetters = array_combine($properties, $properties);
        foreach ($parameters as $parameter) {
            if ($parameter->getType()) {
                $type = $parameter->getType()->getName();
                if (isset($properties[$type])) {
                    $extraSetters[$properties[$type]] = $parameter->getName();
                    unset($extraParams[$type]);
                }
            }
        }
        $parameters = array_map(array($this, '_getMethodParameterInfo'), $parameters);
        foreach ($extraParams as $type => $name) {
            array_unshift($parameters, [
                'name' => $name,
                'type' => $type
            ]);
        }
        foreach ($extraSetters as $name => $paramName) {
            array_unshift($body, "\$this->$name = \$$paramName;");
        }

        $this->classMethods[] = [
            'name' => '__construct',
            'parameters' => $parameters,
            'body' => implode("\n", $body),
            'docblock' => ['shortDescription' => '{@inheritdoc}'],
        ];

    }

    protected function _getClassMethods()
    {
        return $this->classMethods;
    }

    protected function _getClassProperties()
    {
        return $this->classProperties;
    }

    private function addCodeSubBlock(&$body, $sub, $indent = 1)
    {
        foreach ($sub as $line) {
            $body[] = str_repeat("\t", $indent) . $line;
        }
    }

    /**
     * @param \ReflectionMethod $method
     * @param $conf
     * @param $parameters
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getMethodSourceFromConfig(\ReflectionMethod $method, $conf, $parameters, $returnVoid)
    {
        $body = [];
        $first = true;
        $capName = ucfirst($method->getName());
        $extraParams = empty($parameters) ? '' : (', ' . $this->_getParameterList($parameters));

        if (isset($conf[DefinitionInterface::LISTENER_BEFORE])) {
            foreach ($conf[DefinitionInterface::LISTENER_BEFORE] as $plugin) {
                if ($first) $first = false; else $body[] = "";

                $call = "\$this->" . $this->getGetterName($plugin) . "()->before$capName(\$this$extraParams);";

                if (!empty($parameters)) {
                    $body[] = "\$beforeResult = " . $call;
                    $body[] = "if (\$beforeResult !== null) list({$this->_getParameterList($parameters)}) = (array)\$beforeResult;";
                } else {
                    $body[] = $call;
                }
            }
        }


        $chain = [];
        $main = [];
        if (isset($conf[DefinitionInterface::LISTENER_AROUND])) {
            $plugin = $conf[DefinitionInterface::LISTENER_AROUND];
            $main[] = "\$this->" . $this->getGetterName($plugin) . "()->around$capName(\$this, function({$this->_getParameterListForNextCallback($parameters)}){";
            $this->addCodeSubBlock($main, $this->_getMethodSourceFromConfig($method, $plugin['next'] ?: [], $parameters, $returnVoid));
            $main[] = "}$extraParams);";
        } else {
            $main[] = "parent::{$method->getName()}({$this->_getParameterList($parameters)});";
        }
        $chain[] = $main;

        if (isset($conf[DefinitionInterface::LISTENER_AFTER])) {
            foreach ($conf[DefinitionInterface::LISTENER_AFTER] as $plugin) {
                if ($returnVoid) {
                    $chain[] = ["((\$tmp = \$this->" . $this->getGetterName($plugin) . "()->after$capName(\$this, \$result$extraParams)) !== null) ? \$tmp : \$result;"];
                } else {
                    $chain[] = ["\$this->" . $this->getGetterName($plugin) . "()->after$capName(\$this, \$result$extraParams);"];
                }
            }
        }
        foreach ($chain as $lp => $piece) {
            if ($first) $first = false; else $body[] = "";
            if (!$returnVoid) {
                $piece[0] = (($lp + 1 == count($chain)) ? "return " : "\$result = ") . $piece[0];
            }
            foreach ($piece as $line) {
                $body[] = $line;
            }
        }
        
        return $body;
    }

    /**
     * @param \ReflectionParameter[]  $parameters
     * @return string
     */
    protected function _getParameterListForNextCallback(array $parameters)
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
     * @param \ReflectionParameter[]  $parameters
     * @return string
     */
    protected function _getParameterList(array $parameters)
    {
        $ret = [];
        foreach ($parameters as $parameter) {
            $ret [] = "\${$parameter->getName()}";
        }
        return implode(', ', $ret);
    }

    protected function getGetterName($plugin)
    {
        return '____plugin_' . $plugin['clean_name'];
    }

    protected function _getPluginPropertyInfo($plugin)
    {
        return [
            'name' => '____plugin_' . $plugin['clean_name'],
            'visibility' => 'private',
            'docblock' => [
                'tags' => [['name' => 'var', 'description' => '\\' . $plugin['class']]],
            ]
        ];
    }

    protected function _getPluginGetterInfo($plugin)
    {
        $body = [];
        $varName = "\$this->____plugin_" . $plugin['clean_name'];

        $body[] = "if ($varName === null) {";
        $body[] = "\t$varName = \$this->____om->get(\\" . "{$plugin['class']}::class);";
        $body[] = "}";
        $body[] = "return $varName;";

        return [
            'name' => $this->getGetterName($plugin),
            'parameters' => [],
            'body' => implode("\n", $body),
            //'returnType' => $class,
            'docblock' => [
                'shortDescription' => 'plugin "' . $plugin['code'] . '"' . "\n" . '@return \\' . $plugin['class']
            ],
        ];
    }

    protected function _getCompiledMethodInfo(\ReflectionMethod $method, $config)
    {
        $parameters = $method->getParameters();
        $returnsVoid = ($method->hasReturnType() && $method->getReturnType()->getName() == 'void');

        $body = [
            'switch($this->____scope->getCurrentScope()){'
        ];

        $cases = [];
        //group cases by config
        foreach ($config as $scope => $conf) {
            $key = md5(serialize($conf));
            if (!isset($cases[$key])) $cases[$key] = ['cases'=>[], 'conf'=>$conf];
            $cases[$key]['cases'][] = "\tcase '$scope':";
        }
        //call parent method for scopes with no plugins (or when no scope is set)
        $cases[] = ['cases'=>["\tdefault:"], 'conf'=>[]];

        foreach ($cases as $case) {
            $body = array_merge($body, $case['cases']);
            $this->addCodeSubBlock($body, $this->_getMethodSourceFromConfig($method, $case['conf'], $parameters, $returnsVoid), 2);
            //$body[] = "\t\tbreak;";
        }

        $body[] = "}";
        
        return [
            'name' => ($method->returnsReference() ? '& ' : '') . $method->getName(),
            'parameters' =>array_map(array($this, '_getMethodParameterInfo'), $parameters),
            'body' => implode("\n", $body),
            'returnType' => $method->getReturnType(),
            'docblock' => ['shortDescription' => '{@inheritdoc}'],
        ];
    }

    protected function _getPluginInfo(CompiledPluginList $plugins, $code, $className, &$allPlugins, $next = null)
    {
        $className = $plugins->getPluginType($className, $code);
        if (!isset($allPlugins[$code])) $allPlugins[$code] = [];
        if (empty($allPlugins[$code][$className])) {
            $suffix = count($allPlugins[$code]) ? count($allPlugins[$code]) + 1 : '';
            $allPlugins[$code][$className] = [
                'code' => $code,
                'class' => $className,
                'clean_name' => preg_replace("/[^A-Za-z0-9_]/", '_', $code . $suffix)
            ];
        }
        $ret = $allPlugins[$code][$className];
        $ret['next'] = $next;
        return $ret;

    }

    protected function _getPluginsChain(CompiledPluginList $plugins, $className, $method, &$allPlugins, $next = '__self')
    {
        $ret = $plugins->getNext($className, $method, $next);
        if(!empty($ret[DefinitionInterface::LISTENER_BEFORE])) {
            foreach ($ret[DefinitionInterface::LISTENER_BEFORE] as $k => $code) {
                $ret[DefinitionInterface::LISTENER_BEFORE][$k] = $this->_getPluginInfo($plugins, $code, $className, $allPlugins);
            }
        }
        if(!empty($ret[DefinitionInterface::LISTENER_AFTER])) {
            foreach ($ret[DefinitionInterface::LISTENER_AFTER] as $k => $code) {
                $ret[DefinitionInterface::LISTENER_AFTER][$k] = $this->_getPluginInfo($plugins, $code, $className, $allPlugins);
            }
        }
        if (isset($ret[DefinitionInterface::LISTENER_AROUND])) {
            $ret[DefinitionInterface::LISTENER_AROUND] = $this->_getPluginInfo($plugins, $ret[DefinitionInterface::LISTENER_AROUND], $className, $allPlugins,
                $this->_getPluginsChain($plugins, $className, $method, $allPlugins, $ret[DefinitionInterface::LISTENER_AROUND]));
        }
        return $ret;
    }

    protected function _getPluginsConfig(\ReflectionMethod $method, &$allPlugins)
    {
        $className = ltrim($this->getSourceClassName(), '\\');

        $ret = array();
        foreach ($this->plugins as $scope => $pluginsList) {
            $p = $this->_getPluginsChain($pluginsList, $className, $method->getName(), $allPlugins);
            if ($p) {
                $ret[$scope] = $p;
            }

        }
        return $ret;
    }

}
