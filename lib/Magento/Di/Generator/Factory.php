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
 * @category    Magento
 * @package     Magento_Di
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magento_Di_Generator_Factory extends Magento_Di_Generator_EntityAbstract
{
    /**
     * Entity type
     */
    const ENTITY_TYPE = 'factory';

    /**
     * Generic object manager factory interface
     */
    const FACTORY_INTERFACE = '\Magento_ObjectManager_Factory';

    /**
     * @return string
     */
    protected function _generateCode()
    {
        $this->_classGenerator->setImplementedInterfaces(array(self::FACTORY_INTERFACE));

        return parent::_generateCode();
    }

    /**
     * @return array
     */
    protected function _getClassMethods()
    {
        $construct = $this->_getDefaultConstructorDefinition();

        // public function createFromArray(array $data = array())
        $createFromArray = array(
            'name'       => 'createFromArray',
            'parameters' => array(
                array('name' => 'data', 'type' => 'array', 'defaultValue' => array()),
            ),
            'body' => 'return $this->_objectManager->create(self::CLASS_NAME, $data, false);',
            'docblock' => array(
                'shortDescription' => 'Create class instance with specified parameters',
                'tags'             => array(
                    array(
                        'name'        => 'param',
                        'description' => 'array $data'
                    ),
                    array(
                        'name'        => 'return',
                        'description' => $this->_getFullyQualifiedClassName($this->_getSourceClassName())
                    ),
                ),
            ),
        );

        return array($construct, $createFromArray);
    }
}
