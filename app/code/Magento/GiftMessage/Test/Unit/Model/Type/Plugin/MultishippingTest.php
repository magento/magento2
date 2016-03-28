<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Test\Unit\Model\Type\Plugin;

use Magento\GiftMessage\Model\Type\Plugin\Multishipping;

class MultishippingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Multishipping
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
            'Magento\GiftMessage\Model\Type\Plugin\Multishipping',
            [
                'message' => $this->messageMock,
                'request' => $this->requestMock,
            ]
        );
    }

    /**
     * @dataProvider beforeSetShippingMethodsDataProvider
     * @param array|null $methods
     */
    public function testBeforeSetShippingMethods($methods)
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('giftmessage')
            ->will($this->returnValue('Expected Value'));
        $subjectMock = $this->getMock('\Magento\Multishipping\Model\Checkout\Type\Multishipping', [], [], '', false);
        $quoteMock = $this->getMock('\Magento\Quote\Model\Quote', [], [], '', false);
        $subjectMock->expects($this->once())->method('getQuote')->will($this->returnValue($quoteMock));
        $this->messageMock->expects($this->once())->method('add')->with('Expected Value', $quoteMock);

        $this->plugin->beforeSetShippingMethods($subjectMock, $methods);
    }

    /**
     * @return array
     */
    public function beforeSetShippingMethodsDataProvider()
    {
        return [
            [null],
            [[]]
        ];
    }
}
