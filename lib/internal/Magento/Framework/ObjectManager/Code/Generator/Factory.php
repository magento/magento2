<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Code\Generator;

class Factory extends \Magento\Framework\Code\Generator\EntityAbstract
{
    /**
     * Entity type
     */
    const ENTITY_TYPE = 'factory';

    /**
     * Retrieve class properties
     *
     * @return array
     */
    protected function _getClassProperties()
    {
        $properties = parent::_getClassProperties();

        // protected $_instanceName = null;
        $properties[] = [
            'name' => '_instanceName',
            'visibility' => 'protected',
            'docblock' => [
                'shortDescription' => 'Instance name to create',
                'tags' => [['name' => 'var', 'description' => 'string']],
            ],
        ];
        return $properties;
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
                ['name' => 'objectManager', 'type' => '\Magento\Framework\ObjectManagerInterface'],
                ['name' => 'instanceName', 'defaultValue' => $this->getSourceClassName()],
            ],
            'body' => "\$this->_objectManager = \$objectManager;\n\$this->_instanceName = \$instanceName;",
            'docblock' => [
                'shortDescription' => ucfirst(static::ENTITY_TYPE) . ' constructor',
                'tags' => [
                    [
                        'name' => 'param',
                        'description' => '\Magento\Framework\ObjectManagerInterface $objectManager',
                    ],
                    ['name' => 'param', 'description' => 'string $instanceName'],
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

        // public function create(array $data = array())
        $create = [
            'name' => 'create',
            'parameters' => [['name' => 'data', 'type' => 'array', 'defaultValue' => []]],
            'body' => 'return $this->_objectManager->create($this->_instanceName, $data);',
            'docblock' => [
                'shortDescription' => 'Create class instance with specified parameters',
                'tags' => [
                    ['name' => 'param', 'description' => 'array $data'],
                    [
                        'name' => 'return',
                        'description' => $this->getSourceClassName()
                    ],
                ],
            ],
        ];

        return [$construct, $create];
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

            if ($resultClassName !== $sourceClassName . 'Factory') {
                $this->_addError(
                    'Invalid Factory class name [' . $resultClassName . ']. Use ' . $sourceClassName . 'Factory'
                );
                $result = false;
            }
        }
        return $result;
    }
}
