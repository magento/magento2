<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Di;

use Traversable;
use Zend\Di\Definition\ArrayDefinition;
use Zend\Di\Definition\RuntimeDefinition;
use Zend\Stdlib\ArrayUtils;

/**
 * Configures Di instances
 */
class Config
{
    /**
     * @var array
     */
    protected $data = array();

    /**
     * Constructor
     *
     * @param  array|Traversable                  $options
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($options)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        if (!is_array($options)) {
            throw new Exception\InvalidArgumentException(
                'Config data must be of type Traversable or an array'
            );
        }
        $this->data = $options;
    }

    /**
     * Configure
     *
     * @param  Di   $di
     * @return void
     */
    public function configure(Di $di)
    {
        if (isset($this->data['definition'])) {
            $this->configureDefinition($di, $this->data['definition']);
        }
        if (isset($this->data['instance'])) {
            $this->configureInstance($di, $this->data['instance']);
        }
    }

    /**
     * @param Di    $di
     * @param array $definition
     */
    public function configureDefinition(Di $di, $definition)
    {
        foreach ($definition as $definitionType => $definitionData) {
            switch ($definitionType) {
                case 'compiler':
                    foreach ($definitionData as $filename) {
                        if (is_readable($filename)) {
                            $di->definitions()->addDefinition(new ArrayDefinition(include $filename), false);
                        }
                    }
                    break;
                case 'runtime':
                    if (isset($definitionData['enabled']) && !$definitionData['enabled']) {
                        // Remove runtime from definition list if not enabled
                        $definitions = array();
                        foreach ($di->definitions() as $definition) {
                            if (!$definition instanceof RuntimeDefinition) {
                                $definitions[] = $definition;
                            }
                        }
                        $definitionList = new DefinitionList($definitions);
                        $di->setDefinitionList($definitionList);
                    } elseif (isset($definitionData['use_annotations']) && $definitionData['use_annotations']) {
                        /* @var $runtimeDefinition Definition\RuntimeDefinition */
                        $runtimeDefinition = $di
                            ->definitions()
                            ->getDefinitionByType('\Zend\Di\Definition\RuntimeDefinition');
                        $runtimeDefinition->getIntrospectionStrategy()->setUseAnnotations(true);
                    }
                    break;
                case 'class':
                    foreach ($definitionData as $className => $classData) {
                        $classDefinitions = $di->definitions()->getDefinitionsByType('Zend\Di\Definition\ClassDefinition');
                        foreach ($classDefinitions as $classDefinition) {
                            if (!$classDefinition->hasClass($className)) {
                                unset($classDefinition);
                            }
                        }
                        if (!isset($classDefinition)) {
                            $classDefinition = new Definition\ClassDefinition($className);
                            $di->definitions()->addDefinition($classDefinition, false);
                        }
                        foreach ($classData as $classDefKey => $classDefData) {
                            switch ($classDefKey) {
                                case 'instantiator':
                                    $classDefinition->setInstantiator($classDefData);
                                    break;
                                case 'supertypes':
                                    $classDefinition->setSupertypes($classDefData);
                                    break;
                                case 'methods':
                                case 'method':
                                    foreach ($classDefData as $methodName => $methodInfo) {
                                        if (isset($methodInfo['required'])) {
                                            $classDefinition->addMethod($methodName, $methodInfo['required']);
                                            unset($methodInfo['required']);
                                        }
                                        foreach ($methodInfo as $paramName => $paramInfo) {
                                            $classDefinition->addMethodParameter($methodName, $paramName, $paramInfo);
                                        }
                                    }
                                    break;
                                default:
                                    $methodName = $classDefKey;
                                    $methodInfo = $classDefData;
                                    if (isset($classDefData['required'])) {
                                        $classDefinition->addMethod($methodName, $methodInfo['required']);
                                        unset($methodInfo['required']);
                                    }
                                    foreach ($methodInfo as $paramName => $paramInfo) {
                                        $classDefinition->addMethodParameter($methodName, $paramName, $paramInfo);
                                    }
                            }
                        }
                    }
            }
        }
    }

    /**
     * Configures a given Di instance
     *
     * @param Di $di
     * @param $instanceData
     */
    public function configureInstance(Di $di, $instanceData)
    {
        $im = $di->instanceManager();

        foreach ($instanceData as $target => $data) {
            switch (strtolower($target)) {
                case 'aliases':
                case 'alias':
                    foreach ($data as $n => $v) {
                        $im->addAlias($n, $v);
                    }
                    break;
                case 'preferences':
                case 'preference':
                    foreach ($data as $n => $v) {
                        if (is_array($v)) {
                            foreach ($v as $v2) {
                                $im->addTypePreference($n, $v2);
                            }
                        } else {
                            $im->addTypePreference($n, $v);
                        }
                    }
                    break;
                default:
                    foreach ($data as $n => $v) {
                        switch ($n) {
                            case 'parameters':
                            case 'parameter':
                                $im->setParameters($target, $v);
                                break;
                            case 'injections':
                            case 'injection':
                                $im->setInjections($target, $v);
                                break;
                            case 'shared':
                            case 'share':
                                $im->setShared($target, $v);
                                break;
                        }
                    }
            }
        }
    }
}
