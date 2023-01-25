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
use Magento\GiftMessage\Model\Type\Plugin\Onepage;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OnepageTest extends TestCase
{
    /**
     * @var Onepage
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
            Onepage::class,
            [
                'message' => $this->messageMock,
                'request' => $this->requestMock,
            ]
        );
    }

    public function testAfterSaveShippingMethodWithEmptyResult()
    {
        $subjectMock = $this->createMock(\Magento\Checkout\Model\Type\Onepage::class);
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('giftmessage')
            ->willReturn('giftMessage');
        $quoteMock = $this->createMock(Quote::class);
        $subjectMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);
        $this->messageMock->expects($this->once())->method('add')->with('giftMessage', $quoteMock);

        $this->assertEquals([], $this->plugin->afterSaveShippingMethod($subjectMock, []));
    }

    public function testAfterSaveShippingMethodWithNotEmptyResult()
    {
        $subjectMock = $this->createMock(\Magento\Checkout\Model\Type\Onepage::class);
        $this->assertEquals(
            ['expected result'],
            $this->plugin->afterSaveShippingMethod($subjectMock, ['expected result'])
        );
    }
}
