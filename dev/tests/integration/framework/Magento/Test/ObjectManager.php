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
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magento_Test_ObjectManager extends Magento_ObjectManager_Zend
{
    /**
     * Classes with xml properties to explicitly call __destruct() due to https://bugs.php.net/bug.php?id=62468
     *
     * @var array
     */
    protected $_classesToDestruct = array(
        'Mage_Core_Model_Config',
        'Mage_Core_Model_Layout',
        'Mage_Core_Model_Layout_Merge',
        'Mage_Core_Model_Layout_ScheduledStructure',
    );

    /**
     * Clear InstanceManager cache
     *
     * @return Magento_Test_ObjectManager
     */
    public function clearCache()
    {
        foreach ($this->_classesToDestruct as $className) {
            if ($this->hasSharedInstance($className)) {
                $object = $this->get($className);
                if ($object) {
                    // force to cleanup circular references
                    $object->__destruct();
                }
            }
        }

        $resource = $this->get('Mage_Core_Model_Resource');
        $this->_di->setInstanceManager(new Magento_Di_InstanceManager_Zend());
        $this->addSharedInstance($this, 'Magento_ObjectManager');
        $this->addSharedInstance($resource, 'Mage_Core_Model_Resource');

        return $this;
    }
}
