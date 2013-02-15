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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Core_Model_ObjectManagerTest extends PHPUnit_Framework_TestCase
{
    public function testConstructConfiguresObjectManager()
    {
        $this->assertNull(Mage::getObjectManager());
        $configMock = $this->getMock('Magento_ObjectManager_Configuration');
        $configMock->expects($this->once())
            ->method('configure')
            ->with($this->isInstanceOf('Mage_Core_Model_ObjectManager'));
        $diMock = $this->getMock('Magento_Di');
        $imMock = $this->getMock('Magento_Di_InstanceManager');
        $diMock->expects($this->any())->method('instanceManager')->will($this->returnValue($imMock));
        $objectManager = new Mage_Core_Model_ObjectManager($configMock, __DIR__, $diMock);
        $this->assertEquals($objectManager, Mage::getObjectManager());
    }
}
