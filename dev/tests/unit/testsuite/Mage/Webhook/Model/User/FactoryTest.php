<?php
/**
 * Mage_Webhook_Model_User_Factory
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
 * @category    Mage
 * @package     Mage_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webhook_Model_User_FactoryTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $mockObjectManager = $this->getMockBuilder('Magento_ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $factory = new Mage_Webhook_Model_User_Factory($mockObjectManager);

        $mockUser = $this->getMockBuilder('Mage_Webhook_Model_User')
            ->disableOriginalConstructor()
            ->getMock();

        $arguments = array('arg_one', 'arg_two');

        $mockObjectManager->expects($this->once())
            ->method('create')
            ->with('Mage_Webhook_Model_User', $arguments)
            ->will($this->returnValue($mockUser));

        $this->assertSame($mockUser, $factory->create($arguments));
    }

}
