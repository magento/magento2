<?php
/**
 * Test object manager
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Magento_Test_ObjectManager extends Mage_Core_Model_ObjectManager
{
    /**
     * Classes with xml properties to explicitly call __destruct() due to https://bugs.php.net/bug.php?id=62468
     *
     * @var array
     */
    protected $_classesToDestruct = array(
        'Mage_Core_Model_Layout',
    );

    /**
     * Clear InstanceManager cache
     *
     * @return Magento_Test_ObjectManager
     */
    public function clearCache()
    {
        foreach ($this->_classesToDestruct as $className) {
            if ($this->_di->instanceManager()->hasSharedInstance($className)) {
                $object = $this->_di->instanceManager()->getSharedInstance($className);
                if ($object) {
                    // force to cleanup circular references
                    $object->__destruct();
                }
            }
        }

        Mage::getSingleton('Mage_Core_Model_Config_Base')->destroy();
        $instanceManagerNew = new Magento_Di_InstanceManager_Zend();
        $instanceManagerNew->addSharedInstance($this, 'Magento_ObjectManager');
        if ($this->_di->instanceManager()->hasSharedInstance('Mage_Core_Model_Resource')) {
            $resource = $this->_di->instanceManager()->getSharedInstance('Mage_Core_Model_Resource');
            $instanceManagerNew->addSharedInstance($resource, 'Mage_Core_Model_Resource');
        }
        $this->_di->setInstanceManager($instanceManagerNew);

        return $this;
    }
}
