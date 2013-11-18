<?php
/**
 * \Magento\Webhook\Block\Adminhtml\Registration\Failed
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
 * @category    Magento
 * @package     Magento_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webhook\Block\Adminhtml\Registration;

class FailedTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\Webhook\Block\Adminhtml\Registration\Failed */
    private $_block;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $_lastMessage;

    protected function setUp()
    {
        $urlBuilder = $this->getMock('Magento\Core\Model\Url', array('getUrl'), array(), '', false);

        /** @var  $coreData \Magento\Core\Helper\Data */
        $coreData = $this->getMock('Magento\Core\Helper\Data', array(), array(), '', false);

        $context = $this->getMockBuilder('Magento\Backend\Block\Template\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->once())
            ->method('getUrlBuilder')
            ->will($this->returnValue($urlBuilder));

        $this->_lastMessage = $this->getMockBuilder('Magento\Core\Model\Message\AbstractMessage')
            ->disableOriginalConstructor()
            ->getMock();
        $messages = $this->getMockBuilder('Magento\Core\Model\Message\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $messages->expects($this->any())
            ->method('getLastAddedMessage')
            ->will($this->returnValue($this->_lastMessage));
        $session = $this->getMockBuilder('Magento\Backend\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();
        $session->expects($this->once())
            ->method('getMessages')
            ->will($this->returnValue($messages));
        $this->_block = new \Magento\Webhook\Block\Adminhtml\Registration\Failed($coreData, $session, $context);
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
