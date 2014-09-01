<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
    const SEARCH_CRITERIA = '\\Magento\Framework\Service\V1\Data\SearchCriteria';

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
                    'shortDescription' =>  $this->_getSourceFactoryPropertyName(),
                    'tags' => [
                        [
                            'name' => 'var',
                            'description' =>
                                $this->_getFullyQualifiedClassName($this->_getSourceClassName()) . 'Factory'
                        ]
                    ]
                ]
            ],
            [
                'name' => $this->_getSourceCollectionFactoryPropertyName(),
                'visibility' => 'protected',
                'docblock' => [
                    'shortDescription' =>  'Collection Factory',
                    'tags' => [
                        [
                            'name' => 'var',
                            'description' =>
                                $this->_getCollectionFactoryClassName()
                        ]
                    ]
                ]
            ],
            [
                'name' => 'registry',
                'visibility' => 'protected',
                'defaultValue' => [],
                'docblock' => [
                    'shortDescription' => $this->_getSourceClassName() . '[]',
                    'tags' => [
                        [
                            'name' => 'var',
                            'description' => 'array'
                        ]
                    ]
                ]
            ]
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
        $parts = explode('\\', $this->_getSourceClassName());
        return lcfirst(end($parts)) . 'Factory';
    }

    /**
     * Returns source collection factory property name
     * @return string
     */
    protected function _getSourceCollectionFactoryPropertyName()
    {
        $parts = explode('\\', $this->_getSourceClassName());
        return lcfirst(end($parts)) . 'CollectionFactory';
    }

    /**
     * Returns collection factory class name
     *
     * @return string
     */
    protected function _getCollectionFactoryClassName()
    {
        $parts = explode('\\', $this->_getSourceClassName());
        $parts = array_reverse($parts);
        $className = '\\' . array_pop($parts) . '\\' . array_pop($parts) . '\\' . array_pop($parts) . '\\Resource\\';
        $parts = array_reverse($parts);
        return $className . implode('\\', $parts) . '\\CollectionFactory';

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
                    'name' => $this->_getSourceFactoryPropertyName(),
                    'type' => $this->_getFullyQualifiedClassName($this->_getSourceClassName()) . 'Factory'
                ],
                [
                    'name' => $this->_getSourceCollectionFactoryPropertyName(),
                    'type' => $this->_getCollectionFactoryClassName(),
                ],
            ],
            'body' => "\$this->"
                . $this->_getSourceFactoryPropertyName()
                . " = \$" . $this->_getSourceFactoryPropertyName() . ";\n"
                . "\$this->"
                . $this->_getSourceCollectionFactoryPropertyName()
                . " = \$" . $this->_getSourceCollectionFactoryPropertyName() . ";"
            ,
            'docblock' => [
                'shortDescription' => ucfirst(static::ENTITY_TYPE) . ' constructor',
                'tags' => [
                    [
                        'name' => 'param',
                        'description' => '\\' . $this->_getSourceClassName()
                            . " \$" . $this->_getSourceFactoryPropertyName()
                    ],
                    [
                        'name' => 'param',
                        'description' => $this->_getCollectionFactoryClassName()
                            . " \$" . $this->_getSourceCollectionFactoryPropertyName()
                    ]
                ]
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
            . "    \$entity = \$this->" . $this->_getSourceFactoryPropertyName() . "->create()->load(\$id);\n"
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
                    'type' => 'int'
                ]
            ],
            'body' => $body,
            'docblock' => [
                'shortDescription' => 'load entity',
                'tags' => [
                    [
                        'name' => 'param',
                        'description' => 'int $id'
                    ],
                    [
                        'name' => 'return',
                        'description' => $this->_getFullyQualifiedClassName($this->_getSourceClassName()),
                    ],
                    [
                        'name' => 'throws',
                        'description' => self::INPUT_EXCEPTION,
                    ],
                    [
                        'name' => 'throws',
                        'description' => self::NO_SUCH_ENTITY_EXCEPTION,
                    ]
                ]
            ]
        ];
    }

    /**
     * Returns register() method
     *
     * @return string
     */
    protected function _getRegisterMethod()
    {
        $body = "if (\$object->getId() && !isset(\$this->registry[\$object->getId()])) {\n"
            . "    \$object->load(\$object->getId());\n"
            . "    \$this->registry[\$object->getId()] = \$object;\n"
            . "}\nreturn \$this;";
        return [
            'name' => 'register',
            'parameters' => [
                [
                    'name' => 'object',
                    'type' => $this->_getFullyQualifiedClassName($this->_getSourceClassName())
                ]
            ],
            'body' => $body,
            'docblock' => [
                'shortDescription' => 'Register entity',
                'tags' => [
                    [
                        'name' => 'param',
                        'description' => $this->_getFullyQualifiedClassName($this->_getSourceClassName()) . ' $object'
                    ],
                    [
                        'name' => 'return',
                        'description' => $this->_getFullyQualifiedClassName($this->_getResultClassName()),
                    ]
                ]
            ]
        ];
    }

    /**
     * Returns find() method
     *
     * @return string
     */
    protected function _getFindMethod()
    {
        $body = "\$collection = \$this->" . $this->_getSourceCollectionFactoryPropertyName() . "->create();\n"
        . "foreach(\$criteria->getFilterGroups() as \$filterGroup) {\n"
        . "    foreach (\$filterGroup->getFilters() as \$filter) {\n"
        . "        \$condition = \$filter->getConditionType() ? \$filter->getConditionType() : 'eq';\n"
        . "        \$collection->addFieldToFilter(\$filter->getField(), [\$condition => \$filter->getValue()]);\n"
        . "    }\n"
        . "}\n"
        . "\$collection->setCurPage(\$criteria->getCurrentPage());\n"
        . "\$collection->setPageSize(\$criteria->getPageSize());\n"
        . "foreach (\$collection as \$object) {\n"
        . "    \$this->register(\$object);\n"
        . "}\n"
        . "\$objectIds = \$collection->getAllIds();\n"
        . "return array_intersect_key(\$this->registry, array_flip(\$objectIds));\n";
        return [
            'name' => 'find',
            'parameters' => [
                [
                    'name' => 'criteria',
                    'type' => self::SEARCH_CRITERIA
                ]
            ],
            'body' => $body,
            'docblock' => [
                'shortDescription' => 'Find entities by criteria',
                'tags' => [
                    [
                        'name' => 'param',
                        'description' => self::SEARCH_CRITERIA . '  $criteria'
                    ],
                    [
                        'name' => 'return',
                        'description' => $this->_getFullyQualifiedClassName($this->_getSourceClassName()) . '[]',
                    ],
                ]
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
            $this->_getRegisterMethod(),
            $this->_getFindMethod()
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function _validateData()
    {
        $result = parent::_validateData();

        if ($result) {
            $sourceClassName = $this->_getSourceClassName();
            $resultClassName = $this->_getResultClassName();

            if ($resultClassName !== $sourceClassName . 'Repository') {
                $this->_addError(
                    'Invalid Factory class name [' . $resultClassName . ']. Use ' . $sourceClassName . 'Repository'
                );
                $result = false;
            }
        }
        return $result;
    }
}
