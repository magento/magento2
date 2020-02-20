<?php
declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\AdminNotification\Block\Grid\Renderer\Actions
 */
namespace Magento\AdminNotification\Test\Unit\Block\Grid\Renderer;

use Magento\AdminNotification\Block\Grid\Renderer\Notice;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Backend\Block\Context;
use PHPUnit\Framework\TestCase;

class NoticeTest extends TestCase
{
    /**
     * System under Test
     *
     * @var Notice
     */
    private $sut;

    protected function setUp() : void
    {
        parent::setUp();

        /** @var Escaper | \PHPUnit_Framework_MockObject_MockObject $escaperMock */
        $escaperMock = $this->getMockBuilder(Escaper::class)->disableOriginalConstructor()->getMock();
        $escaperMock->expects($this->exactly(2))->method('escapeHtml')->willReturn('<div>Some random html</div>');

        /** @var Context | \PHPUnit_Framework_MockObject_MockObject $contextMock */
        $contextMock = $this->getMockBuilder(Context::class)->disableOriginalConstructor()->getMock();
        $contextMock->expects($this->once())->method('getEscaper')->willReturn($escaperMock);

        $this->sut = new Notice($contextMock);
    }

    public function testShouldRenderNotice() : void
    {
        $dataObject = new DataObject();
        $dataObject->setData('title', 'A great Title');
        $dataObject->setData('description', 'Handy description right here');

        $actual = $this->sut->render($dataObject);
        $expected = '<span class="grid-row-title"><div>Some random html</div></span><br /><div>Some random html</div>';

        $this->assertEquals($actual, $expected);
    }
}
