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
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test theme observer
 */
class Mage_Core_Model_ObserverTest extends PHPUnit_Framework_TestCase
{
    /**
     * Theme registration test
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testThemeRegistration()
    {
        $baseDir = 'base_dir';
        $pattern = 'path_pattern';

        $eventObserver = $this->_createEventObserverForThemeRegistration();
        $eventObserver->getEvent()->setBaseDir($baseDir);
        $eventObserver->getEvent()->setPathPattern($pattern);

        /** @var $objectManager Magento_Test_ObjectManager */
        $objectManager = Mage::getObjectManager();
        $themeRegistration = $this->getMock(
            'Mage_Core_Model_Theme_Registration',
            array('register'),
            array($objectManager->create('Mage_Core_Model_Theme'))
        );
        $themeRegistration->expects($this->once())
            ->method('register')
            ->with($baseDir, $pattern);
        $objectManager->addSharedInstance($themeRegistration, 'Mage_Core_Model_Theme_Registration');

        /** @var $observer Mage_Core_Model_Observer */
        $observer = Mage::getModel('Mage_Core_Model_Observer');
        $observer->themeRegistration($eventObserver);
    }

    /**
     * Get theme model
     *
     * @return Mage_Core_Model_Theme
     */
    protected function _getThemeModel()
    {
        return Mage::getModel('Mage_Core_Model_Theme');
    }

    /**
     * Create event observer for theme registration
     *
     * @return Varien_Event_Observer
     */
    protected function _createEventObserverForThemeRegistration()
    {
        $response = Mage::getModel('Varien_Object', array('additional_options' => array()));
        $event = Mage::getModel('Varien_Event', array('response_object' => $response));
        return Mage::getModel('Varien_Event_Observer', array('event' => $event));
    }
}
