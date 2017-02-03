<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Code\Generator;

/**
 * Class Repository
 */
class Mapper extends \Magento\Framework\Code\Generator\EntityAbstract
{
    /**
     * Entity type
     */
    const ENTITY_TYPE = 'mapper';

    /**
     * Retrieve class properties
     *
     * @return array
     */
    protected function _getClassProperties()
    {
        $properties = [
            [
                'name' => $this->_getSourceBuilderPropertyName(),
                'visibility' => 'protected',
                'docblock' => [
                    'shortDescription' =>  $this->_getSourceBuilderPropertyName(),
                    'tags' => [
                        [
                            'name' => 'var',
                            'description' => $this->getSourceClassName() . 'Builder',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'registry',
                'visibility' => 'protected',
                'defaultValue' => [],
                'docblock' => [
                    'shortDescription' => $this->getSourceClassName() . '[]',
                    'tags' => [['name' => 'var', 'description' => 'array']],
                ]
            ],
        ];
        return $properties;
    }

    /**
     * Returns source factory property Name
     *
     * @return string
     */
    protected function _getSourceBuilderPropertyName()
    {
        return lcfirst($this->getSourceClassNameWithoutNamespace()) . 'Builder';
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
                    'name' => $this->_getSourceBuilderPropertyName(),
                    'type' => $this->getSourceClassName() . 'Builder',
                ],
            ],
            'body' => "\$this->"
                . $this->_getSourceBuilderPropertyName()
                . " = \$" . $this->_getSourceBuilderPropertyName() . ';',
            'docblock' => [
                'shortDescription' => ucfirst(static::ENTITY_TYPE) . ' constructor',
                'tags' => [
                    [
                        'name' => 'param',
                        'description' => $this->getSourceClassName() . " \$" . $this->_getSourceBuilderPropertyName(),
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
        $construct = $this->_getDefaultConstructorDefinition();
        $body = "\$this->" . $this->_getSourceBuilderPropertyName() . "->populateWithArray(\$object->getData());"
            . "\nreturn \$this->" . $this->_getSourceBuilderPropertyName() . "->create();";
        $extract = [
            'name' => 'extractDto',
            'parameters' => [
                [
                    'name' => 'object',
                    'type' => '\\Magento\Framework\Model\AbstractModel',
                ],
            ],
            'body' => $body,
            'docblock' => [
                'shortDescription' => 'Extract data object from model',
                'tags' => [
                    [
                        'name' => 'param',
                        'description' => '\\Magento\Framework\Model\AbstractModel $object',
                    ],
                    [
                        'name' => 'return',
                        'description' => $this->getSourceClassName(),
                    ],
                ],
            ],
        ];
        return [$construct, $extract];
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

            if ($resultClassName !== $sourceClassName . 'Mapper') {
                $this->_addError(
                    'Invalid Mapper class name [' . $resultClassName . ']. Use ' . $sourceClassName . 'Mapper'
                );
                $result = false;
            }
        }
        return $result;
    }
}
