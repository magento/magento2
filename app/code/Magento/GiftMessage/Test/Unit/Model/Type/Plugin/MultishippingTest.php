<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GiftMessage\Test\Unit\Model\Type\Plugin;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GiftMessage\Model\GiftMessageManager;
use Magento\GiftMessage\Model\Type\Plugin\Multishipping;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MultishippingTest extends TestCase
{
    /**
     * @var Multishipping
     */
    protected $plugin;

    /**
     * @var MockObject
     */
    protected $messageMock;

    /**
     * @var MockObject
     */
    protected $requestMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->messageMock = $this->createMock(GiftMessageManager::class);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);

        $this->plugin = $objectManager->getObject(
            Multishipping::class,
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
        $quoteMock = $this->createMock(Quote::class);
        $subjectMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);
        $this->messageMock->expects($this->once())->method('add')->with('Expected Value', $quoteMock);

        $this->plugin->beforeSetShippingMethods($subjectMock, $methods);
    }

    /**
     * @return array
     */
    public static function beforeSetShippingMethodsDataProvider()
    {
        return [
            [null],
            [[]]
        ];
    }
}
