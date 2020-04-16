<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
        $inlineMock = $this->createPartialMock(
            Inline::class,
            ['setId', 'setDontDisplayContainer', 'setEntity', 'setCheckoutType', 'toHtml']
        );

        $this->layoutFactoryMock->expects($this->once())->method('create')->will($this->returnValue($layoutMock));
        $layoutMock->expects($this->once())->method('createBlock')->will($this->returnValue($inlineMock));

        $inlineMock->expects($this->once())->method('setId')->will($this->returnSelf());
        $inlineMock->expects($this->once())->method('setDontDisplayContainer')->will($this->returnSelf());
        $inlineMock->expects($this->once())->method('setEntity')->with($entityMock)->will($this->returnSelf());
        $inlineMock->expects($this->once())->method('setCheckoutType')->will($this->returnSelf());
        $inlineMock->expects($this->once())->method('toHtml')->will($this->returnValue($expectedHtml));

        $this->assertEquals($expectedHtml, $this->helper->getInline('onepage_checkout', $entityMock));
    }
}
