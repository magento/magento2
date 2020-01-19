<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\ObjectManager\Code\Generator;

use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;
use Zend\Code\Reflection\MethodReflection;
use Zend\Code\Reflection\ParameterReflection;

/**
 * Class Repository
 * @since 2.0.0
 * @deprecated 2.2.0 As current implementation breaks Repository contract. Not removed from codebase to prevent
 * possible backward incompatibilities if this functionality being used by 3rd party developers.
 */
class Repository extends \Magento\Framework\Code\Generator\EntityAbstract
{
    /**
     * Entity type
     */
    const ENTITY_TYPE = 'repository';

    /**
     * The namespace of repository interface
     * @var string
     */
    private $interfaceName;

    /**
     * List of interface methods.
     *
     * @var array
     */
    private $methodList = [];

    /**
     * Retrieve class properties
     *
     * @return array
     */
    protected function _getClassProperties()
    {
        $properties = [
            [
                'name' => $this->_getSourcePersistorPropertyName(),
                'visibility' => 'protected',
                'docblock' => [
                    'shortDescription' => $this->_getSourcePersistorPropertyName(),
                    'tags' => [
                        [
                            'name' => 'var',
                            'description' => $this->_getPersistorClassName(),
                        ],
                    ],
                ],
            ],
            [
                'name' => $this->_getSourceCollectionFactoryPropertyName(),
                'visibility' => 'protected',
                'docblock' => [
                    'shortDescription' => 'Collection Factory',
                    'tags' => [
                        [
                            'name' => 'var',
                            'description' => $this->_getCollectionFactoryClassName(),
                        ],
                    ],
                ]
            ],
            [
                'name' => 'registry',
                'visibility' => 'protected',
                'defaultValue' => [],
                'docblock' => [
                    'shortDescription' => $this->getSourceClassName() . '[]',
                    'tags' => [
                        [
                            'name' => 'var',
                            'description' => 'array',
                        ],
                    ],
                ]
            ],
            [
                'name' => 'extensionAttributesJoinProcessor',
                'visibility' => 'protected',
                'docblock' => [
                    'shortDescription' => 'Extension attributes join processor.',
                    'tags' => [
                        [
                            'name' => 'var',
                            'description' => '\\' . JoinProcessorInterface::class,
                        ],
                    ],
                ]
            ],
            [
                'name' => 'collectionProcessor',
                'visibility' => 'private',
                'docblock' => [
                    'shortDescription' => 'Search Criteria Collection processor.',
                    'tags' => [
                        [
                            'name' => 'var',
                            'description' => '\\' . CollectionProcessorInterface::class,
                        ],
                    ],
                ]
            ],
        ];
        return $properties;
    }

    /**
     * Returns source factory property name
     *
     * @return string
     */
    protected function _getSourcePersistorPropertyName()
    {
        return lcfirst($this->getSourceClassNameWithoutNamespace()) . 'Persistor';
    }

    /**
     * Returns source collection factory property name
     * @return string
     */
    protected function _getSourceCollectionFactoryPropertyName()
    {
        return lcfirst($this->getSourceClassNameWithoutNamespace()) . 'SearchResultFactory';
    }

    /**
     * Returns collection factory class name
     *
     * @return string
     */
    protected function _getCollectionFactoryClassName()
    {
        return
            str_replace('Interface', '', $this->getSourceClassName()) . 'SearchResultInterfaceFactory';
    }

    /**
     * Returns source persistor class name
     *
     * @return string
     */
    protected function _getPersistorClassName()
    {
        $target = $this->getSourceClassName();
        return $target . 'Persistor';
    }

    /**
     * Get default constructor definition for generated class
     *
     * @return array
     */
    protected function _getDefaultConstructorDefinition()
    {
        return [
            'name' => '__construct',
            'parameters' => [
                [
                    'name' => $this->_getSourcePersistorPropertyName(),
                    'type' => $this->_getPersistorClassName(),
                ],
                [
                    'name' => $this->_getSourceCollectionFactoryPropertyName(),
                    'type' => $this->_getCollectionFactoryClassName(),
                ],
                [
                    'name' => 'extensionAttributesJoinProcessor',
                    'type' => '\\' . JoinProcessorInterface::class,
                ],
            ],
            'body' => "\$this->"
                . $this->_getSourcePersistorPropertyName()
                . " = \$" . $this->_getSourcePersistorPropertyName() . ";\n"
                . "\$this->"
                . $this->_getSourceCollectionFactoryPropertyName()
                . " = \$" . $this->_getSourceCollectionFactoryPropertyName() . ";\n"
                . "\$this->extensionAttributesJoinProcessor = \$extensionAttributesJoinProcessor;"
            ,
            'docblock' => [
                'shortDescription' => ucfirst(static::ENTITY_TYPE) . ' constructor',
                'tags' => [
                    [
                        'name' => 'param',
                        'description' => $this->getSourceClassName() . " \$" . $this->_getSourcePersistorPropertyName(),
                    ],
                    [
                        'name' => 'param',
                        'description' => $this->_getCollectionFactoryClassName()
                            . " \$" . $this->_getSourceCollectionFactoryPropertyName(),
                    ],
                    [
                        'name' => 'param',
                        'description' => '\\' . JoinProcessorInterface::class . " \$extensionAttributesJoinProcessor",
                    ],
                ],
            ]
        ];
    }

    /**
     * Returns get() method
     *
     * @return array
     */
    protected function _getGetMethod()
    {
        $interfaceName = $this->getInterfaceName();
        $methodReflection = new MethodReflection($interfaceName, 'get');
        /** @var ParameterReflection $parameterReflection */
        $parameterReflection = $methodReflection->getParameters()[0];
        $body = "if (!\$id) {\n"
            . "    throw new \\" . InputException::class . "(\n"
            . "        new \\Magento\\Framework\\Phrase('An ID is needed. Set the ID and try again.')\n"
            . "    );\n"
            . "}\n"
            . "if (!isset(\$this->registry[\$id])) {\n"
            . "    \$entity = \$this->" . $this->_getSourcePersistorPropertyName()
            . "->loadEntity(\$id);\n"
            . "    if (!\$entity->getId()) {\n"
            . "        throw new \\" . NoSuchEntityException::class . "(\n"
            . "            new \\Magento\\Framework\\Phrase(\n"
            . "                'The entity that was requested doesn\'t exist. Verify the entity and try again.'\n"
            . "            )\n"
            . "        );\n"
            . "    }\n"
            . "    \$this->registry[\$id] = \$entity;\n"
            . "}\n"
            . "return \$this->registry[\$id];";
        return [
            'name' => 'get',
            'parameters' => [
                [
                    'name' => 'id',
                    'type' => $parameterReflection->getType(),
                ],
            ],
            'body' => $body,
            'returnType' => $methodReflection->getReturnType(),
            'docblock' => [
                'shortDescription' => 'load entity',
                'tags' => [
                    [
                        'name' => 'param',
                        'description' => 'int $id',
                    ],
                    [
                        'name' => 'return',
                        'description' => $this->getSourceClassName(),
                    ],
                    [
                        'name' => 'throws',
                        'description' => '\\' . InputException::class,
                    ],
                    [
                        'name' => 'throws',
                        'description' => '\\' . NoSuchEntityException::class,
                    ],
                    [
                        'name' => 'deprecated'
                    ],
                ],
            ]
        ];
    }

    /**
     * Returns register() method
     *
     * @return array
     */
    protected function _getCreateFromArrayMethod()
    {
        $body = "return \$this->{$this->_getSourcePersistorPropertyName()}->registerFromArray(\$data);";
        return [
            'name' => 'createFromArray',
            'parameters' => [
                [
                    'name' => 'data',
                    'type' => 'array',
                ],
            ],
            'body' => $body,
            'docblock' => [
                'shortDescription' => 'Register entity to create',
                'tags' => [
                    [
                        'name' => 'param',
                        'description' => 'array $data',
                    ],
                    [
                        'name' => 'return',
                        'description' => $this->_getResultClassName(),
                    ],
                    [
                        'name' => 'deprecated'
                    ],
                ],
            ]
        ];
    }

    /**
     * Returns register() method
     *
     * @return array
     */
    protected function _getCreateMethod()
    {
        $body = "return \$this->{$this->_getSourcePersistorPropertyName()}->registerNew(\$entity);";
        return [
            'name' => 'create',
            'parameters' => [
                [
                    'name' => 'entity',
                    'type' => $this->getSourceClassName(),
                ],
            ],
            'body' => $body,
            'docblock' => [
                'shortDescription' => 'Register entity to create',
                'tags' => [
                    [
                        'name' => 'param',
                        'description' => 'array $data',
                    ],
                    [
                        'name' => 'return',
                        'description' => $this->getSourceClassName(),
                    ],
                    [
                        'name' => 'deprecated'
                    ]
                ],
            ]
        ];
    }

    /**
     * Returns register() method
     *
     * @return array
     */
    protected function _getFlushMethod()
    {
        $body = "\$ids = \$this->{$this->_getSourcePersistorPropertyName()}->doPersist();\n"
            . "foreach (\$ids as \$id) {\n"
            . "unset(\$this->registry[\$id]);\n"
            . "}";
        return [
            'name' => 'flush',
            'parameters' => [],
            'body' => $body,
            'docblock' => [
                'shortDescription' => 'Perform persist operations',
                'tags' => [
                    [
                        'name' => 'deprecated'
                    ],
                ],
            ]
        ];
    }

    /**
     * Returns persist() method
     *
     * @return array
     */
    protected function _getSaveMethod()
    {
        $info = $this->getMethodParamAndReturnType('save');
        $body = "\$this->{$this->_getSourcePersistorPropertyName()}->doPersistEntity(\$entity);\n"
            . "return \$entity;";
        return [
            'name' => 'save',
            'parameters' => [
                [
                    'name' => 'entity',
                    'type' => $this->getSourceClassName(),
                ],
            ],
            'body' => $body,
            'returnType' => $info['returnType'],
            'docblock' => [
                'shortDescription' => 'Perform persist operations for one entity',
                'tags' => [
                    [
                        'name' => 'param',
                        'description' => $this->getSourceClassName() . " \$entity",
                    ],
                    [
                        'name' => 'return',
                        'description' => $this->getSourceClassName(),
                    ],
                    [
                        'name' => 'deprecated'
                    ],
                ],
            ]
        ];
    }

    /**
     * Return remove() method
     *
     * @return array
     */
    protected function _getDeleteMethod()
    {
        $info = $this->getMethodParamAndReturnType('delete');
        $body = "\$this->{$this->_getSourcePersistorPropertyName()}->registerDeleted(\$entity);\n"
            . "return \$this->{$this->_getSourcePersistorPropertyName()}->doPersistEntity(\$entity);";
        return [
            'name' => 'delete',
            'parameters' => [
                [
                    'name' => 'entity',
                    'type' => $this->getSourceClassName(),
                ],
            ],
            'returnType' => $info['returnType'],
            'body' => $body,
            'docblock' => [
                'shortDescription' => 'Register entity to delete',
                'tags' => [
                    [
                        'name' => 'param',
                        'description' => $this->getSourceClassName() . ' $entity',
                    ],
                    [
                        'name' => 'return',
                        'description' => 'bool',
                    ],
                    [
                        'name' => 'deprecated'
                    ],
                ],
            ]
        ];
    }

    /**
     * Return remove() method
     *
     * @return array
     */
    protected function _getDeleteByIdMethod()
    {
        $info = $this->getMethodParamAndReturnType('deleteById');
        $body = "\$entity = \$this->get(\$id);\n"
            . "\$this->{$this->_getSourcePersistorPropertyName()}->registerDeleted(\$entity);\n"
            . "return \$this->{$this->_getSourcePersistorPropertyName()}->doPersistEntity(\$entity);";
        return [
            'name' => 'deleteById',
            'parameters' => [
                [
                    'name' => 'id',
                    'type' => $info['paramType'],
                ],
            ],
            'body' => $body,
            'returnType' => $info['returnType'],
            'docblock' => [
                'shortDescription' => 'Delete entity by Id',
                'tags' => [
                    [
                        'name' => 'param',
                        'description' => 'int $id',
                    ],
                    [
                        'name' => 'return',
                        'description' => 'bool',
                    ],
                    [
                        'name' => 'deprecated'
                    ],
                ],
            ]
        ];
    }

    /**
     * Return remove() method
     *
     * @return array
     */
    protected function _getRemoveMethod()
    {
        $body = "\$this->{$this->_getSourcePersistorPropertyName()}->registerDeleted(\$entity);";
        return [
            'name' => 'remove',
            'parameters' => [
                [
                    'name' => 'entity',
                    'type' => $this->getSourceClassName(),
                ],
            ],
            'body' => $body,
            'docblock' => [
                'shortDescription' => 'Register entity to delete',
                'tags' => [
                    [
                        'name' => 'param',
                        'description' => $this->getSourceClassName() . ' $entity',
                    ],
                    [
                        'name' => 'deprecated'
                    ],
                ],
            ]
        ];
    }

    /**
     * Returns getList() method
     *
     * @return array
     */
    protected function _getGetListMethod()
    {
        $body = "\$collection = \$this->" . $this->_getSourceCollectionFactoryPropertyName() . "->create();\n"
        . "\$this->extensionAttributesJoinProcessor->process(\$collection);\n"
        . "\$this->getCollectionProcessor()->process(\$searchCriteria, \$collection);\n"
        . "return \$collection;\n";
        return [
            'name' => 'getList',
            'parameters' => [
                [
                    'name' => 'searchCriteria',
                    'type' => '\\' . SearchCriteriaInterface::class,
                ],
            ],
            'body' => $body,
            'docblock' => [
                'shortDescription' => 'Find entities by criteria',
                'tags' => [
                    [
                        'name' => 'param',
                        'description' => '\\' . SearchCriteriaInterface::class . ' $searchCriteria',
                    ],
                    [
                        'name' => 'return',
                        'description' => $this->getSourceClassName() . '[]',
                    ],
                    [
                        'name' => 'deprecated'
                    ],
                ],
            ]
        ];
    }

    /**
     * Returns getList() method
     *
     * @return string
     */
    private function _getGetCollectionProcessorMethod()
    {
        $body = "if (!\$this->collectionProcessor) {\n"
            . "    \$this->collectionProcessor = \\Magento\\Framework\\App\\ObjectManager::getInstance()->get(\n"
            . "        \\" . CollectionProcessorInterface::class . "::class\n"
            . "    );\n"
            . "}\n"
            . "return \$this->collectionProcessor;\n";
        return [
            'name' => 'getCollectionProcessor',
            'visibility' => 'private',
            'parameters' => [],
            'body' => $body,
            'docblock' => [
                'shortDescription' => 'Retrieve collection processor',
                'tags' => [
                    [
                        'name' => 'deprecated',
                    ],
                    [
                        'name' => 'return',
                        'description' => "\\" . CollectionProcessorInterface::class,
                    ],
                ],
            ]
        ];
    }

    /**
     * Returns list of methods for class generator
     *
     * @return array
     */
    protected function _getClassMethods()
    {
        return [
            $this->_getDefaultConstructorDefinition(),
            $this->_getGetMethod(),
            $this->_getCreateMethod(),
            $this->_getCreateFromArrayMethod(),
            $this->_getGetListMethod(),
            $this->_getRemoveMethod(),
            $this->_getDeleteMethod(),
            $this->_getDeleteByIdMethod(),
            $this->_getFlushMethod(),
            $this->_getSaveMethod(),
            $this->_getGetCollectionProcessorMethod(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function _validateData()
    {
        $result = parent::_validateData();

        if ($result) {
            $sourceClassName = $this->getSourceClassName();
            $resultClassName = $this->_getResultClassName();

            if ($resultClassName !== str_replace('Interface', '', $sourceClassName) . '\\Repository') {
                $this->_addError(
                    'Invalid Factory class name [' . $resultClassName . ']. Use ' . $sourceClassName . 'Repository'
                );
                $result = false;
            }
        }
        return $result;
    }

    /**
     * Generate code
     *
     * @return string
     */
    protected function _generateCode()
    {
        $this->_classGenerator->setName($this->_getResultClassName())
            ->addProperties($this->_getClassProperties())
            ->addMethods($this->_getClassMethods())
            ->setClassDocBlock($this->_getClassDocBlock())
            ->setImplementedInterfaces([$this->getInterfaceName()]);
        return $this->_getGeneratedCode();
    }

    /**
     * @inheritdoc
     */
    protected function _getClassDocBlock()
    {
        $docBlock = parent::_getClassDocBlock();
        $docBlock['tags'] = [
            ['name' => 'deprecated', 'description' => '2.2.0'],
            ['name' => 'see', 'description' => '\\' . self::class]
        ];
        return $docBlock;
    }

    /**
     * Get source class name
     *
     * @return string
     */
    public function getSourceClassName()
    {
        return parent::getSourceClassName() . 'Interface';
    }

    /**
     * Gets name of implementation interface.
     *
     * @return string
     */
    private function getInterfaceName()
    {
        if ($this->interfaceName === null) {
            $this->interfaceName = str_replace(
                'Interface',
                'RepositoryInterface',
                str_replace('Data\\', '', $this->getSourceClassName())
            );
        }

        return $this->interfaceName;
    }

    /**
     * Gets reflection method's first parameter type and return type from implementation interface.
     * Method returns only first parameter because Magento repository interfaces by design have only one parameter
     * in methods.
     *
     * @param string $methodName
     * @return array in ['paramType' => ..., 'returnType' => ...] format
     */
    private function getMethodParamAndReturnType($methodName)
    {
        $result = [
            'paramType' => null,
            'returnType' => null
        ];
        $interfaceName = $this->getInterfaceName();
        $methods = $this->getClassMethods($interfaceName);
        if (!in_array($methodName, $methods)) {
            return $result;
        }

        $methodReflection = new MethodReflection($this->getInterfaceName(), $methodName);
        $params = $methodReflection->getParameters();
        if (!empty($params[0])) {
            /** @var ParameterReflection $parameterReflection */
            $parameterReflection = $params[0];
            $result['paramType'] = $parameterReflection->getType();
        }
        $result['returnType'] = $methodReflection->getReturnType();

        return $result;
    }

    /**
     * Gets list of class methods.
     *
     * @param string $name the class namespace
     * @return array
     */
    private function getClassMethods($name)
    {
        if (empty($this->methodList)) {
            $this->methodList = get_class_methods($name);
        }
        return $this->methodList;
    }
}
