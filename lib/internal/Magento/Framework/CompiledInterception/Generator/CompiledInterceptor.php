<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\CompiledInterception\Generator;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Code\Generator\EntityAbstract;
use Magento\Framework\Interception\Code\Generator\Interceptor;
use Magento\Framework\Interception\DefinitionInterface;

class CompiledInterceptor extends Interceptor
{

    protected $plugins;

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
                $this->plugins[$scope] = new CompiledPluginList($scope);
            }
        }
    }

    public function setInterceptedMethods($interceptedMethods)
    {
        //DUMMY
    }

    protected function _getClassProperties()
    {
        return [];
    }

    protected function _generateCode()
    {
        $typeName = $this->getSourceClassName();
        $reflection = new \ReflectionClass($typeName);

        if ($reflection->isInterface()) {
            $this->_classGenerator->setImplementedInterfaces([$typeName]);
        } else {
            $this->_classGenerator->setExtendedClass($typeName);
        }

        $this->_classGenerator->addUse(ObjectManager::class);
        $this->_classGenerator->addUse(\Magento\Framework\Config\Scope::class);
        //return parent::_generateCode();
        return EntityAbstract::_generateCode();
    }

    protected function _getClassMethods()
    {
        $reflectionClass = new \ReflectionClass($this->getSourceClassName());
        $publicMethods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);

        $methods = [];
        $allPlugins = [];
        foreach ($publicMethods as $method) {
            if ($this->isInterceptedMethod($method)) {
                $config = $this->_getPluginsConfig($method, $allPlugins);
                if (!empty($config)) {
                    $methods[] = $this->_getCompiledMethodInfo($method, $config);
                }
            }
        }
        if (!empty($methods) && !empty($allPlugins)) {
            foreach ($allPlugins as $key => $plugins) {
                foreach ($plugins as $plugin) {
                    $methods[] = $this->_getPluginGetterInfo($plugin);
                }
            }
        }

        return $methods;
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
                //$body[] = "/** @var \\" . "{$plugin['class']} \$plugin {$plugin['code']} */";
                //$body[] = "\$plugin = \$this->" . $this->getGetterName($plugin) . "();";

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
            //$body[] = "/** @var \\" . "{$plugin['class']} \$plugin {$plugin['code']} */";
            //$body[] = "\$plugin = \$this->" . $this->getGetterName($plugin) . "();";
            $main[] = "\$this->" . $this->getGetterName($plugin) . "()->around$capName(\$this, function({$this->_getParameterListForNextCallback($parameters)}){";
            $this->addCodeSubBlock($main, $this->_getMethodSourceFromConfig($method, $plugin['next'] ?: [], $parameters, $returnVoid));
            //$body[] = "\treturn \$result;";
            $main[] = "}$extraParams);";
        } else {
            $main[] = "parent::{$method->getName()}({$this->_getParameterList($parameters)});";
        }
        $chain[] = $main;

        if (isset($conf[DefinitionInterface::LISTENER_AFTER])) {
            foreach ($conf[DefinitionInterface::LISTENER_AFTER] as $plugin) {
                //$body[] = "/** @var \\" . "{$plugin['class']} \$plugin {$plugin['code']} */";
                //$body[] = "\$plugin = \$this->" . $this->getGetterName($plugin) . "();";
                if ($returnVoid) {
                    $chain[] = ["((\$tmp = \$this->" . $this->getGetterName($plugin) . "()->after$capName(\$this, \$result$extraParams)) !== null) ? \$tmp : \$result;"];
                } else {
                    $chain[] = ["\$this->" . $this->getGetterName($plugin) . "()->after$capName(\$this, \$result$extraParams);"];
                }
            }
        }
        foreach ($chain as $lp => $piece) {
            //if ($first) $first = false; else $body[] = "";
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
        return '_get_plugin_' . preg_replace("/[^A-Za-z0-9_]/", '_', $plugin['code'] . $plugin['suffix']);
    }

    protected function _getPluginGetterInfo($plugin)
    {
        $body = [];

        $body[] = "static \$cache = null;";
        $body[] = "if (\$cache === null) \$cache = ObjectManager::getInstance()->get(\\" . "{$plugin['class']}::class);";
        $body[] = "return \$cache;";

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
            'switch(ObjectManager::getInstance()->get(Scope::class)->getCurrentScope()){'
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

        foreach($cases as $case) {
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
            $allPlugins[$code][$className] = [
                'code' => $code,
                'class' => $className,
                'suffix' => count($allPlugins[$code]) ? count($allPlugins[$code]) + 1 : ''
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
