<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\ObjectManager\Code\Generator;

/**
 * Class Persistor
 */
class Persistor extends \Magento\Framework\Code\Generator\EntityAbstract
{
    /**
     * Entity type
     */
    const ENTITY_TYPE = 'persistor';

    /**
     * Retrieve class properties
     *
     * @return array
     */
    protected function _getClassProperties()
    {
        $properties = [
            [
                'name' => $this->_getSourceFactoryPropertyName(),
                'visibility' => 'protected',
                'docblock' => [
                    'shortDescription' =>  'Entity factory',
                    'tags' => [
                        [
                            'name' => 'var',
                            'description' => $this->getSourceClassName() . 'Factory',
                        ],
                    ],
                ],
            ],
            [
                'name' => $this->_getSourceResourcePropertyName(),
                'visibility' => 'protected',
                'docblock' => [
                    'shortDescription' =>  'Resource model',
                    'tags' => [
                        [
                            'name' => 'var',
                            'description' => $this->_getSourceResourceClassName(),
                        ],
                    ],
                ]
            ],
            [
                'name' => 'resource',
                'visibility' => 'protected',
                'docblock' => [
                    'shortDescription' =>  'Application Resource',
                    'tags' => [
                        [
                            'name' => 'var',
                            'description' => '\Magento\Framework\App\ResourceConnection',
                        ],
                    ],
                ]
            ],
            [
                'name' => 'connection',
                'visibility' => 'protected',
                'docblock' => [
                    'shortDescription' =>  'Database Adapter',
                    'tags' => [
                        [
                            'name' => 'var',
                            'description' => '\Magento\Framework\DB\Adapter\AdapterInterface',
                        ],
                    ],
                ]
            ],
            [
                'name' => 'entitiesPool',
                'visibility' => 'protected',
                'defaultValue' => [],
                'docblock' => [
                    'shortDescription' => '',
                    'tags' => [
                        [
                            'name' => 'var',
                            'description' => 'array',
                        ],
                    ],
                ]
            ],
            [
                'name' => 'stack',
                'visibility' => 'protected',
                'defaultValue' => [],
                'docblock' => [
                    'shortDescription' => '',
                    'tags' => [
                        [
                            'name' => 'var',
                            'description' => 'array',
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
    protected function _getSourceFactoryPropertyName()
    {
        return lcfirst($this->getSourceClassNameWithoutNamespace()) . 'Factory';
    }

    /**
     * Returns source collection factory property name
     * @return string
     */
    protected function _getSourceResourcePropertyName() // InvoiceResource
    {
        return lcfirst($this->getSourceClassNameWithoutNamespace()) . "Resource";
    }

    /**
     * Returns collection factory class name
     *
     * @return string
     */
    protected function _getSourceResourceClassName() // Invoice\Resource
    {
        $temporary = str_replace('\\Api\\Data\\', '\\Model\\Spi\\', $this->getSourceClassName());
        $parts = explode('\\', ltrim($temporary, '\\'));
        $className = array_pop($parts);
        $className = str_replace('Interface', '', $className);
        return '\\' . implode('\\', $parts) . '\\' . $className . 'ResourceInterface';
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
            $this->_getGetConnectionMethod(),
            $this->_getLoadEntityMethod(),
            $this->_getRegisterDeletedMethod(),
            $this->_getRegisterNewMethod(),
            $this->_getRegisterFromArrayMethod(),
            $this->_getDoPersistMethod(),
            $this->_getDoPersistEntityMethod()
        ];
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
                    'name' => $this->_getSourceResourcePropertyName(),
                    'type' => $this->_getSourceResourceClassName(),
                ],
                [
                    'name' => $this->_getSourceFactoryPropertyName(),
                    'type' => $this->getSourceClassName() . 'Factory'
                ],
                [
                    'name' => 'resource',
                    'type' => '\Magento\Framework\App\ResourceConnection'
                ],
            ],
            'body' => "\$this->"
                . $this->_getSourceResourcePropertyName()
                . " = \$" . $this->_getSourceResourcePropertyName() . ";\n"
                . "\$this->"
                . $this->_getSourceFactoryPropertyName()
                . " = \$" . $this->_getSourceFactoryPropertyName() . ";\n"
                . "\$this->resource = \$resource;"
            ,
            'docblock' => [
                'shortDescription' => ucfirst(static::ENTITY_TYPE) . ' constructor',
                'tags' => [
                    [
                        'name' => 'param',
                        'description' => $this->_getSourceResourceClassName()
                            . " \$" . $this->_getSourceResourcePropertyName(),
                    ],
                    [
                        'name' => 'param',
                        'description' => $this->getSourceClassName() . 'Factory'
                            . " \$" . $this->_getSourceFactoryPropertyName()
                    ],
                    [
                        'name' => 'param',
                        'description' => '\Magento\Framework\App\ResourceConnection $resource'
                    ],
                ],
            ]
        ];
    }

    /**
     * @return array
     */
    protected function _getGetConnectionMethod()
    {
        $body = "if (!\$this->connection) {\n"
        . "    \$this->connection = \$this->resource->getConnection("
        . "\\Magento\\Framework\\App\\ResourceConnection::DEFAULT_CONNECTION);\n"
        . "}\n"
        . "return \$this->connection;";

        return [
            'name' => 'getConnection',
            'parameters' => [],
            'body' => $body,
            'docblock' => [
                'shortDescription' => 'Returns Adapter interface',
                'tags' => [
                    [
                        'name' => 'return',
                        'description' => "array \\Magento\\Framework\\DB\\Adapter\\AdapterInterface",
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
    protected function _getLoadEntityMethod()
    {
        $body = "\$entity = \$this->{$this->_getSourceFactoryPropertyName()}->create()->load(\$key);\n"
            . "return \$entity;";
        return [
            'name' => 'loadEntity',
            'parameters' => [
                [
                    'name' => 'key',
                ],
            ],
            'body' => $body,
            'docblock' => [
                'shortDescription' => 'Load entity by key',
                'tags' => [
                    [
                        'name' => 'param',
                        'description' => "int \$key",
                    ],
                    [
                        'name' => 'return',
                        'description' => $this->_getResultClassName() . " \$entity"
                    ],
                ],
            ]
        ];
    }

    /**
     * Returns registerDelete() method
     *
     * @return string
     */
    protected function _getRegisterDeletedMethod()
    {
        $body = "\$hash = spl_object_hash(\$entity);\n"
            . "array_push(\$this->stack, \$hash);\n"
            . "\$this->entitiesPool[\$hash] = [\n"
            . "    'entity' => \$entity,\n"
            . "    'action' => 'removed'\n"
            . "];";
        return [
            'name' => 'registerDeleted',
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
                        'description' => $this->getSourceClassName() . " \$entity",
                    ],
                ],
            ]
        ];
    }

    /**
     * @return array
     */
    protected function _getDoPersistMethod()
    {
        $body = "\$ids = [];\n"
            . "\$this->getConnection()->beginTransaction();\n"
            . "try {\n"
            . "    do {\n"
            . "        \$hash = array_pop(\$this->stack);\n"
            . "        if (isset(\$this->entitiesPool[\$hash])) {\n"
            . "            \$data = \$this->entitiesPool[\$hash];\n"
            . "            \$entity = \$data['entity'];\n"
            . "            if (\$data['action'] == 'created') {\n"
            . "                \$this->{$this->_getSourceResourcePropertyName()}->save(\$entity);\n"
            . "                \$ids[] = \$entity->getId();\n"
            . "            } else {\n"
            . "                \$ids[] = \$entity->getId();\n"
            . "                \$this->{$this->_getSourceResourcePropertyName()}->delete(\$entity);\n"
            . "            }\n"
            . "        }\n"
            . "        unset(\$this->entitiesPool[\$hash]);\n"
            . "        \$items--;\n"
            . "    } while (!empty(\$this->entitiesPool) || \$items === 0);\n"
            . "    \$this->getConnection()->commit();\n"
            . "    return \$ids;\n"
            . "} catch (\\Exception \$e) {\n"
            . "    \$this->getConnection()->rollback();\n"
            . "    throw \$e;\n"
            . "}";
        return [
            'name' => 'doPersist',
            'parameters' => [
                [
                    'name' => 'items',
                    'defaultValue' => 0,

                ],
            ],
            'body' => $body,
            'docblock' => [
                'shortDescription' => 'Perform persist operation',
                'tags' => [
                    [
                        'name' => 'param',
                        'description' => "int \$items",
                    ],
                    [
                        'name' => 'return',
                        'description' => "array",
                    ],
                ],
            ]
        ];
    }

    /**
     * Returns registerDelete() method
     *
     * @return string
     */
    protected function _getDoPersistEntityMethod()
    {
        $body = "\$hash = spl_object_hash(\$entity);\n"
            . "\$action = 'created';\n"
            . "if (isset(\$this->entitiesPool[\$hash])) {\n"
            . "     \$action = \$this->entitiesPool[\$hash]['action'];\n"
            . "     \$tempStack = \$this->stack;\n"
            . "     array_flip(\$tempStack);\n"
            . "     unset(\$tempStack[\$hash]);\n"
            . "     \$this->stack = array_flip(\$tempStack);\n"
            . "     unset(\$this->entitiesPool[\$hash]);\n"
            . "}\n"
            . "\$action == 'created' ? \$this->registerNew(\$entity) : \$this->registerDeleted(\$entity);\n"
            . "return \$this->doPersist(1);";
        return [
            'name' => 'doPersistEntity',
            'parameters' => [
                [
                    'name' => 'entity',
                    'type' => $this->getSourceClassName(),
                ],
            ],
            'body' => $body,
            'docblock' => [
                'shortDescription' => 'Persist entity',
                'tags' => [
                    [
                        'name' => 'param',
                        'description' => $this->getSourceClassName() . " \$entity",
                    ],
                ],
            ]
        ];
    }

    /**
     * Returns registerDelete() method
     *
     * @return string
     */
    protected function _getRegisterFromArrayMethod()
    {
        $body = "\$entity = \$this->{$this->_getSourceFactoryPropertyName()}->create(['data' => \$data]);\n"
            . "\$this->registerNew(\$entity);\n"
            . "return \$entity;";
        return [
            'name' => 'registerFromArray',
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
                        'description' => "array \$data",
                    ],
                    [
                        'name' => 'param',
                        'description' => $this->getSourceClassName() . " \$entity",
                    ],
                ],
            ]
        ];
    }

    /**
     * Returns registerDelete() method
     *
     * @return string
     */
    protected function _getRegisterNewMethod()
    {
        $body = "\$hash = spl_object_hash(\$entity);\n"
            . "\$data = [\n"
            . "     'entity' => \$entity,\n"
            . "     'action' => 'created'\n"
            . "];\n"
            . "array_push(\$this->stack, \$hash);\n"
            . "\$this->entitiesPool[\$hash] = \$data;";
        return [
            'name' => 'registerNew',
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
                        'description' => $this->getSourceClassName() . " \$entity",
                    ],
                ],
            ]
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

            if ($resultClassName !== $sourceClassName . 'Persistor') {
                $this->_addError(
                    'Invalid Factory class name [' . $resultClassName . ']. Use ' . $sourceClassName . 'Persistor'
                );
                $result = false;
            }
        }
        return $result;
    }
}
