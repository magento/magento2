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
namespace Magento\Framework\Mail;

class MessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject
     */
    protected $_messageMock;

    public function setUp()
    {
        $this->_messageMock = $this->getMock(
            '\Magento\Framework\Mail\Message',
            array('getBodyText', 'getBodyHtml', 'setBodyText', 'setBodyHtml')
        );
    }

    /**
     * @param string $messageType
     * @param string $method
     *
     * @covers \Magento\Framework\Mail\Message::setBody
     * @covers \Magento\Framework\Mail\Message::setMessageType
     * @dataProvider setBodyDataProvider
     */
    public function testSetBody($messageType, $method)
    {
        $this->_messageMock->setMessageType($messageType);

        $this->_messageMock->expects($this->once())
            ->method($method)
            ->with('body');

        $this->_messageMock->setBody('body');
    }

    /**
     * @return array
     */
    public function setBodyDataProvider()
    {
        return array(
            array(
                'messageType' => 'text/plain',
                'method' => 'setBodyText'
            ),
            array(
                'messageType' => 'text/html',
                'method' => 'setBodyHtml'
            )
        );
    }

    /**
     * @param string $messageType
     * @param string $method
     *
     * @covers \Magento\Framework\Mail\Message::getBody
     * @covers \Magento\Framework\Mail\Message::setMessageType
     * @dataProvider getBodyDataProvider
     */
    public function testGetBody($messageType, $method)
    {
        $this->_messageMock->setMessageType($messageType);

        $this->_messageMock->expects($this->once())
            ->method($method);

        $this->_messageMock->getBody('body');
    }

    /**
     * @return array
     */
    public function getBodyDataProvider()
    {
        return array(
            array(
                'messageType' => 'text/plain',
                'method' => 'getBodyText'
            ),
            array(
                'messageType' => 'text/html',
                'method' => 'getBodyHtml'
            )
        );
    }
}
