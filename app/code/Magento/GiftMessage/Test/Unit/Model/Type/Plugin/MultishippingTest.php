<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
        $this->messageMock = $this->getMock(\Magento\GiftMessage\Model\GiftMessageManager::class, [], [], '', false);
        $this->requestMock = $this->getMock(\Magento\Framework\App\RequestInterface::class);

        $this->plugin = $objectManager->getObject(
            \Magento\GiftMessage\Model\Type\Plugin\Multishipping::class,
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
        $subjectMock = $this->getMock(
            \Magento\Multishipping\Model\Checkout\Type\Multishipping::class,
            [],
            [],
            '',
            false
        );
        $quoteMock = $this->getMock(\Magento\Quote\Model\Quote::class, [], [], '', false);
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
