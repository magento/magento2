<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Block\Adminhtml\Widget\Grid\Column\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Link;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LinkTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var Escaper|MockObject
     */
    protected $escaperMock;

    /**
     * @var UrlInterface|MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var Link
     */
    protected $linkRenderer;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->escaperMock = $this->createMock(Escaper::class);
        $this->escaperMock->expects($this->any())->method('escapeHtml')->willReturnArgument(0);
        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->urlBuilderMock->expects($this->once())->method('getUrl')->willReturn('http://magento.loc/linkurl');
        $this->contextMock = $this->createPartialMock(
            Context::class,
            ['getEscaper', 'getUrlBuilder']
        );
        $this->contextMock->expects($this->any())->method('getEscaper')->willReturn($this->escaperMock);
        $this->contextMock->expects($this->any())
            ->method('getUrlBuilder')
            ->willReturn($this->urlBuilderMock);

        $this->objectManagerHelper = new ObjectManager($this);
        $this->linkRenderer = $this->objectManagerHelper->getObject(
            Link::class,
            ['context' => $this->contextMock]
        );
    }

    /**
     * Test the basic render action.
     *
     * @return void
     */
    public function testRender(): void
    {
        $expectedResult = '<a href="http://magento.loc/linkurl" title="Link Caption">Link Caption</a>';
        $column = $this->getMockBuilder(Column::class)->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->addMethods(['getCaption'])
            ->getMock();
        $column->expects($this->any())
            ->method('getCaption')
            ->willReturn('Link Caption');
        $column->expects($this->any())
            ->method('getId')
            ->willReturn('1');
        $this->escaperMock
            ->method('escapeHtmlAttr')
            ->willReturn('Link Caption');
        $this->linkRenderer->setColumn($column);
        $object = new DataObject(['id' => '1']);
        $actualResult = $this->linkRenderer->render($object);
        $this->assertEquals($expectedResult, $actualResult);
    }
}
