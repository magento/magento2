<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\GiftMessage\Test\Unit\Model\Type\Plugin;

use Magento\GiftMessage\Model\Type\Plugin\Onepage;

class OnepageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Onepage
     */
    protected $plugin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->messageMock = $this->getMock('\Magento\GiftMessage\Model\GiftMessageManager', [], [], '', false);
        $this->requestMock = $this->getMock('\Magento\Framework\App\RequestInterface');

        $this->plugin = $objectManager->getObject(
            'Magento\GiftMessage\Model\Type\Plugin\Onepage',
            [
                'message' => $this->messageMock,
                'request' => $this->requestMock,
            ]
        );
    }

    public function testAfterSaveShippingMethodWithEmptyResult()
    {
        $subjectMock = $this->getMock('\Magento\Checkout\Model\Type\Onepage', [], [], '', false);
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('giftmessage')
            ->will($this->returnValue('giftMessage'));
        $quoteMock = $this->getMock('\Magento\Quote\Model\Quote', [], [], '', false);
        $subjectMock->expects($this->once())->method('getQuote')->will($this->returnValue($quoteMock));
        $this->messageMock->expects($this->once())->method('add')->with('giftMessage', $quoteMock);

        $this->assertEquals([], $this->plugin->afterSaveShippingMethod($subjectMock, []));
    }

    public function testAfterSaveShippingMethodWithNotEmptyResult()
    {
        $subjectMock = $this->getMock('\Magento\Checkout\Model\Type\Onepage', [], [], '', false);
        $this->assertEquals(
            ['expected result'],
            $this->plugin->afterSaveShippingMethod($subjectMock, ['expected result']));
    }
}
