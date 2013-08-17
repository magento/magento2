<?php
/**
 * Mage_Webhook_Block_Adminhtml_Registration_Activate
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
class Mage_Webhook_Block_Adminhtml_Registration_ActivateTest extends PHPUnit_Framework_TestCase
{
    /** @var  Mage_Webhook_Block_Adminhtml_Registration_Activate */
    private $_block;

    /** @var Mage_Core_Model_Url */
    private $_urlBuilder;

    /** @var array  */
    private $_subscription = array(
        'subscription_id' => 333,
        'name' => 'test_subscription',
        'topics' => array('customer/created', 'customer/updated')
    );

    public function setUp()
    {
        $this->_urlBuilder = $this->getMock('Mage_Core_Model_Url', array('getUrl'), array(), '', false);
        /** @var Mage_Core_Block_Template_Context $context */
        $context = $this->getMock('Mage_Backend_Block_Template_Context', array('getUrlBuilder'), array(), '', false);
        $context->expects($this->once())
            ->method('getUrlBuilder')
            ->will($this->returnValue($this->_urlBuilder));

        $registry = $this->getMock('Mage_Core_Model_Registry', array('registry'), array(), '', false);
        $registry->expects($this->once())
            ->method('registry')
            ->with('current_subscription')
            ->will($this->returnValue($this->_subscription));
        $this->_block = new Mage_Webhook_Block_Adminhtml_Registration_Activate($context, $registry);
    }

    public function testGetAcceptUrl()
    {
        $url = 'example.url.com/id/333';
        $this->_urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('*/*/accept', array('id' => 333))
            ->will($this->returnValue($url));

        $this->assertEquals($url, $this->_block->getAcceptUrl());
    }

    public function testGetSubscriptionName()
    {
        $this->assertEquals($this->_subscription['name'], $this->_block->getSubscriptionName());
    }

    public function testGetSubscriptionTopics()
    {
        $this->assertEquals($this->_subscription['topics'], $this->_block->getSubscriptionTopics());
    }
}