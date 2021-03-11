<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Test\Unit\Model\Type\Plugin;

use Magento\GiftMessage\Model\Type\Plugin\Multishipping;

class MultishippingTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Multishipping
     */
    protected $plugin;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $messageMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->messageMock = $this->createMock(\Magento\GiftMessage\Model\GiftMessageManager::class);
        $this->requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);

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
            ->willReturn('Expected Value');
        $subjectMock = $this->createMock(\Magento\Multishipping\Model\Checkout\Type\Multishipping::class);
        $quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);
        $subjectMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);
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
