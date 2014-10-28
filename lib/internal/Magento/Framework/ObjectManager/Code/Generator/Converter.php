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
 * Class Converter
 * @package Magento\Framework\ObjectManager\Code\Generator
 */
class Converter extends \Magento\Framework\Code\Generator\EntityAbstract
{
    /**
     * Entity type
     */
    const ENTITY_TYPE = 'converter';

    /**
     * Retrieve class properties
     *
     * @return array
     */
    protected function _getClassProperties()
    {
        return [
            [
                'name' => $this->_getFactoryPropertyName(),
                'visibility' => 'protected',
                'docblock' => [
                    'shortDescription' => $this->_getFactoryPropertyName(),
                    'tags' => [
                        [
                            'name' => 'var',
                            'description' =>
                                $this->_getFactoryClass()
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Returns factory name
     *
     * @return string
     */
    protected function _getFactoryPropertyName()
    {
        $parts = explode('\\', $this->_getSourceClassName());
        return lcfirst(end($parts)) . 'Factory';
    }

    /**
     * Returns factory class
     *
     * @return string
     */
    protected function _getFactoryClass()
    {
        return $this->_getFullyQualifiedClassName($this->_getSourceClassName()) . 'Factory';
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
                    'name' => $this->_getFactoryPropertyName(),
                    'type' => $this->_getFactoryClass()
                ],
            ],
            'body' => "\$this->"
                . $this->_getFactoryPropertyName()
                . " = \$" . $this->_getFactoryPropertyName() . ';',
            'docblock' => [
                'shortDescription' => ucfirst(static::ENTITY_TYPE) . ' constructor',
                'tags' => [
                    [
                        'name' => 'param',
                        'description' => '\\' . $this->_getSourceClassName()
                            . " \$" . $this->_getFactoryPropertyName()
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
        $paramName = 'dataObject';
        $body = 'return $this->' . $this->_getFactoryPropertyName()
            . '->create()->setData($' . $paramName .'->__toArray());';
        $getModel = [
            'name' => 'getModel',
            'parameters' => [
                [
                    'name' => $paramName,
                    'type' => '\Magento\Framework\Service\Data\AbstractExtensibleObject'
                ]
            ],
            'body' => $body,
            'docblock' => [
                'shortDescription' => 'Extract data object from model',
                'tags' => [
                    [
                        'name' => 'param',
                        'description' => '\Magento\Framework\Service\Data\AbstractExtensibleObject $' . $paramName,
                    ],
                    [
                        'name' => 'return',
                        'description' => $this->_getFullyQualifiedClassName($this->_getSourceClassName())
                    ]
                ]
            ]
        ];
        return array($construct, $getModel);
    }

    /**
     * {@inheritdoc}
     */
    protected function _validateData()
    {
        if (!parent::_validateData()) {
            return false;
        }

        $sourceClassName = $this->_getSourceClassName();
        $resultClassName = $this->_getResultClassName();

        if ($resultClassName !== $sourceClassName . 'Converter') {
            $this->_addError(
                'Invalid Converter class name [' . $resultClassName . ']. Use ' . $sourceClassName . 'Converter'
            );
            return false;
        }
        return true;
    }
}
