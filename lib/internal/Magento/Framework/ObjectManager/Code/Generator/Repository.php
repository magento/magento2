<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\ObjectManager\Code\Generator;

/**
 * Class Repository
 */
class Repository extends \Magento\Framework\Code\Generator\EntityAbstract
{
    /**
     * Entity type
     */
    const ENTITY_TYPE = 'repository';

    /**
     * No Such Entity Exception
     */
    const NO_SUCH_ENTITY_EXCEPTION = '\\Magento\Framework\Exception\NoSuchEntityException';
    const INPUT_EXCEPTION = '\\Magento\Framework\Exception\InputException';
    const SEARCH_CRITERIA = '\\Magento\Framework\Api\SearchCriteria';

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
                            'description' => '\Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface',
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
//        if (substr($target, -9) == 'Interface') {
//            $target = substr($target, 1, strlen($target) -9);
//        }
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
                    'type' => '\Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface',
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
                            . " \$" . $this->_getSourceCollectionFactoryPropertyName()
                    ],
                    [
                        'name' => 'param',
                        'description' => '\Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface'
                            . " \$extensionAttributesJoinProcessor"
                    ],
                ],
            ]
        ];
    }

    /**
     * Returns get() method
     *
     * @return string
     */
    protected function _getGetMethod()
    {
        $body = "if (!\$id) {\n"
            . "    throw new " . self::INPUT_EXCEPTION . "('ID required');\n"
            . "}\n"
            . "if (!isset(\$this->registry[\$id])) {\n"
            . "    \$entity = \$this->" . $this->_getSourcePersistorPropertyName()
            . "->loadEntity(\$id);\n"
            . "    if (!\$entity->getId()) {\n"
            . "        throw new " . self::NO_SUCH_ENTITY_EXCEPTION . "('Requested entity doesn\\'t exist');\n"
            . "    }\n"
            . "    \$this->registry[\$id] = \$entity;\n"
            . "}\n"
            . "return \$this->registry[\$id];";
        return [
            'name' => 'get',
            'parameters' => [
                [
                    'name' => 'id',
                    'type' => 'int',
                ],
            ],
            'body' => $body,
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
                        'description' => self::INPUT_EXCEPTION,
                    ],
                    [
                        'name' => 'throws',
                        'description' => self::NO_SUCH_ENTITY_EXCEPTION,
                    ],
                ],
            ]
        ];
    }

    /**
     * Returns register() method
     *
     * @return string
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
                ],
            ]
        ];
    }

    /**
     * Returns register() method
     *
     * @return string
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
                ],
            ]
        ];
    }

    /**
     * Returns register() method
     *
     * @return string
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
                'tags' => [],
            ]
        ];
    }

    /**
     * Returns persist() method
     *
     * @return string
     */
    protected function _getSaveMethod()
    {
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
        $body = "\$entity = \$this->get(\$id);\n"
            . "\$this->{$this->_getSourcePersistorPropertyName()}->registerDeleted(\$entity);\n"
            . "return \$this->{$this->_getSourcePersistorPropertyName()}->doPersistEntity(\$entity);";
        return [
            'name' => 'deleteById',
            'parameters' => [
                [
                    'name' => 'id',
                    'type' => 'int',
                ],
            ],
            'body' => $body,
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
                ],
            ]
        ];
    }

    /**
     * Returns getList() method
     *
     * @return string
     */
    protected function _getGetListMethod()
    {
        $body = "\$collection = \$this->" . $this->_getSourceCollectionFactoryPropertyName() . "->create();\n"
        . "\$this->extensionAttributesJoinProcessor->process(\$collection);\n"
        . "foreach (\$searchCriteria->getFilterGroups() as \$filterGroup) {\n"
        . "    foreach (\$filterGroup->getFilters() as \$filter) {\n"
        . "        \$condition = \$filter->getConditionType() ? \$filter->getConditionType() : 'eq';\n"
        . "        \$collection->addFieldToFilter(\$filter->getField(), [\$condition => \$filter->getValue()]);\n"
        . "    }\n"
        . "}\n"
        . "\$collection->setCurPage(\$searchCriteria->getCurrentPage());\n"
        . "\$collection->setPageSize(\$searchCriteria->getPageSize());\n"
        . "return \$collection;\n";
        return [
            'name' => 'getList',
            'parameters' => [
                [
                    'name' => 'searchCriteria',
                    'type' => self::SEARCH_CRITERIA,
                ],
            ],
            'body' => $body,
            'docblock' => [
                'shortDescription' => 'Find entities by criteria',
                'tags' => [
                    [
                        'name' => 'param',
                        'description' => self::SEARCH_CRITERIA . ' $searchCriteria',
                    ],
                    [
                        'name' => 'return',
                        'description' => $this->getSourceClassName() . '[]',
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
            $this->_getSaveMethod()
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
        $className = str_replace('Interface', '', str_replace('Data\\', '', $this->getSourceClassName()));
        $this->_classGenerator->setName(
            $this->_getResultClassName()
        )->addProperties(
            $this->_getClassProperties()
        )->addMethods(
            $this->_getClassMethods()
        )->setClassDocBlock(
            $this->_getClassDocBlock()
        )->setImplementedInterfaces(
            [
                $className . 'RepositoryInterface',
            ]
        );
        return $this->_getGeneratedCode();
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
}
