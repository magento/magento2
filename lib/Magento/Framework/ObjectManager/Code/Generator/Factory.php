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
        $properties[] = array(
            'name' => '_instanceName',
            'visibility' => 'protected',
            'docblock' => array(
                'shortDescription' => 'Instance name to create',
                'tags' => array(array('name' => 'var', 'description' => 'string'))
            )
        );
        return $properties;
    }

    /**
     * Get default constructor definition for generated class
     *
     * @return array
     */
    protected function _getDefaultConstructorDefinition()
    {
        return array(
            'name' => '__construct',
            'parameters' => array(
                array('name' => 'objectManager', 'type' => '\Magento\Framework\ObjectManager'),
                array('name' => 'instanceName', 'defaultValue' => $this->_getSourceClassName())
            ),
            'body' => "\$this->_objectManager = \$objectManager;\n\$this->_instanceName = \$instanceName;",
            'docblock' => array(
                'shortDescription' => ucfirst(static::ENTITY_TYPE) . ' constructor',
                'tags' => array(
                    array('name' => 'param', 'description' => '\Magento\Framework\ObjectManager $objectManager'),
                    array('name' => 'param', 'description' => 'string $instanceName')
                )
            )
        );
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
        $create = array(
            'name' => 'create',
            'parameters' => array(array('name' => 'data', 'type' => 'array', 'defaultValue' => array())),
            'body' => 'return $this->_objectManager->create($this->_instanceName, $data);',
            'docblock' => array(
                'shortDescription' => 'Create class instance with specified parameters',
                'tags' => array(
                    array('name' => 'param', 'description' => 'array $data'),
                    array(
                        'name' => 'return',
                        'description' => $this->_getFullyQualifiedClassName($this->_getSourceClassName())
                    )
                )
            )
        );

        return array($construct, $create);
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
