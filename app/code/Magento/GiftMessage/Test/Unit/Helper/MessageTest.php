<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GiftMessage\Test\Unit\Helper;

use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use Magento\Framework\View\LayoutFactory;
use Magento\GiftMessage\Block\Message\Inline;
use Magento\GiftMessage\Helper\Message;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $layoutFactoryMock;

    /**
     * @var Message
     */
    protected $helper;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->layoutFactoryMock = $this->createMock(LayoutFactory::class);

        $this->helper = $objectManager->getObject(
            Message::class,
            [
                'layoutFactory' => $this->layoutFactoryMock,
                'skipMessageCheck' => ['onepage_checkout']
            ]
        );
    }

    /**
     * Make sure that isMessagesAllowed is not called
     */
    public function testGetInlineForCheckout()
    {
        $expectedHtml = '<a href="here">here</a>';
        $layoutMock = $this->createMock(Layout::class);
        $entityMock = $this->createMock(DataObject::class);
        $inlineMock = $this->getMockBuilder(Inline::class)
            ->addMethods(['setId', 'setDontDisplayContainer'])
            ->onlyMethods(['setEntity', 'setCheckoutType', 'toHtml'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->layoutFactoryMock->expects($this->once())->method('create')->willReturn($layoutMock);
        $layoutMock->expects($this->once())->method('createBlock')->willReturn($inlineMock);

        $inlineMock->expects($this->once())->method('setId')->willReturnSelf();
        $inlineMock->expects($this->once())->method('setDontDisplayContainer')->willReturnSelf();
        $inlineMock->expects($this->once())->method('setEntity')->with($entityMock)->willReturnSelf();
        $inlineMock->expects($this->once())->method('setCheckoutType')->willReturnSelf();
        $inlineMock->expects($this->once())->method('toHtml')->willReturn($expectedHtml);

        $this->assertEquals($expectedHtml, $this->helper->getInline('onepage_checkout', $entityMock));
    }
}
