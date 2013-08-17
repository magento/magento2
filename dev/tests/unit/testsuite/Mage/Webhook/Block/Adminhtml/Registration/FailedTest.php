<?php
/**
 * Mage_Webhook_Block_Adminhtml_Registration_Failed
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
class Mage_Webhook_Block_Adminhtml_Registration_FailedTest extends PHPUnit_Framework_TestCase
{
    /** @var  Mage_Webhook_Block_Adminhtml_Registration_Failed */
    private $_block;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    private $_lastMessage;

    public function setUp()
    {
        $urlBuilder = $this->getMock('Mage_Core_Model_Url', array('getUrl'), array(), '', false);

        $context = $this->getMockBuilder('Mage_Backend_Block_Template_Context')
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->once())
            ->method('getUrlBuilder')
            ->will($this->returnValue($urlBuilder));

        $this->_lastMessage = $this->getMockBuilder('Mage_Core_Model_Message_Abstract')
            ->disableOriginalConstructor()
            ->getMock();
        $messages = $this->getMockBuilder('Mage_Core_Model_Message_Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $messages->expects($this->any())
            ->method('getLastAddedMessage')
            ->will($this->returnValue($this->_lastMessage));
        $session = $this->getMockBuilder('Mage_Backend_Model_Session')
            ->disableOriginalConstructor()
            ->getMock();
        $session->expects($this->once())
            ->method('getMessages')
            ->will($this->returnValue($messages));
        $this->_block = new Mage_Webhook_Block_Adminhtml_Registration_Failed($session, $context);
    }

    public function testGetSessionError()
    {
        $errorMessage = 'Some error message';
        $this->_lastMessage->expects($this->once())
            ->method('toString')
            ->will($this->returnValue($errorMessage));

        $this->assertEquals($errorMessage, $this->_block->getSessionError());
    }

}