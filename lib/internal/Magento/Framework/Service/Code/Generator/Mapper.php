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
namespace Magento\Framework\Service\Code\Generator;

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
                            'description' =>
                                $this->_getFullyQualifiedClassName($this->_getSourceClassName()) . 'Builder'
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
                    'tags' => [['name' => 'var', 'description' => 'array']]
                ]
            ]
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
        $parts = explode('\\', $this->_getSourceClassName());
        return lcfirst(end($parts)) . 'Builder';
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
                    'type' => $this->_getFullyQualifiedClassName($this->_getSourceClassName()) . 'Builder'
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
                        'description' => '\\' . $this->_getSourceClassName()
                            . " \$" . $this->_getSourceBuilderPropertyName()
                    ]
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
        $construct = $this->_getDefaultConstructorDefinition();
        $body = "\$this->" . $this->_getSourceBuilderPropertyName() . "->populateWithArray(\$object->getData());"
            . "\nreturn \$this->" . $this->_getSourceBuilderPropertyName() . "->create();";
        $extract = [
            'name' => 'extractDto',
            'parameters' => [
                [
                    'name' => 'object',
                    'type' => '\\Magento\Framework\Model\AbstractModel'
                ]
            ],
            'body' => $body,
            'docblock' => [
                'shortDescription' => 'Extract data object from model',
                'tags' => [
                    [
                        'name' => 'param',
                        'description' => '\\Magento\Framework\Model\AbstractModel $object'
                    ],
                    [
                        'name' => 'return',
                        'description' => $this->_getFullyQualifiedClassName($this->_getSourceClassName()),
                    ]
                ]
            ]
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
            $sourceClassName = $this->_getSourceClassName();
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
