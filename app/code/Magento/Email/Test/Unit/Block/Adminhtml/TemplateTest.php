<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Email\Test\Unit\Block\Adminhtml;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Button\Item;
use Magento\Backend\Block\Widget\Button\ItemFactory;
use Magento\Email\Block\Adminhtml\Template;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers Magento\Email\Block\Adminhtml\Template
 */
class TemplateTest extends TestCase
{
    /** @var Template */
    protected $template;

    /** @var Context */
    protected $context;

    /** @var UrlInterface|MockObject */
    protected $urlBuilderMock;

    /** @var ItemFactory|MockObject */
    protected $itemFactoryMock;

    /** @var ButtonList */
    protected $buttonList;

    /** @var Item|MockObject */
    protected $buttonMock;

    /** @var ObjectManager */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->itemFactoryMock = $this->getMockBuilder(ItemFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->buttonMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->buttonMock);
        $this->buttonList = $this->objectManager->getObject(
            ButtonList::class,
            [ 'itemFactory' => $this->itemFactoryMock]
        );
        $this->urlBuilderMock = $this->getMockForAbstractClass(
            UrlInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getUrl']
        );
        $this->context = $this->objectManager->getObject(
            Context::class,
            [
                'urlBuilder' => $this->urlBuilderMock
            ]
        );
        $this->template = $this->objectManager->getObject(
            Template::class,
            [
                'context' => $this->context,
                'buttonList' => $this->buttonList
            ]
        );
    }

    public function testAddButton()
    {
        $this->template->addButton('myButton', ['title' => 'My Button']);
        $buttons = $this->buttonList->getItems()[0];
        $this->assertArrayHasKey('myButton', $buttons);
    }

    public function testUpdateButton()
    {
        $this->template->addButton('myButton', ['title' => 'My Button']);

        $this->buttonMock->expects($this->once())
            ->method('setData')
            ->with('title', 'Updated Button')
            ->willReturnSelf();
        $result = $this->template->updateButton('myButton', 'title', 'Updated Button');
        $this->assertSame($this->template, $result);
    }

    public function testRemoveButton()
    {
        $this->template->addButton('myButton', ['title' => 'My Button']);

        $this->template->removeButton('myButton');
        $buttons = $this->buttonList->getItems()[0];
        $this->assertArrayNotHasKey('myButton', $buttons);
    }

    public function testGetCreateUrl()
    {
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('adminhtml/*/new', []);
        $this->template->getCreateUrl();
    }

    public function testGetHeaderText()
    {
        $this->assertEquals('Transactional Emails', $this->template->getHeaderText());
    }

    public function testCanRender()
    {
        $this->buttonMock->expects($this->once())
            ->method('isDeleted')
            ->willReturn(false);
        $this->assertTrue($this->template->canRender($this->buttonMock));
    }
}
