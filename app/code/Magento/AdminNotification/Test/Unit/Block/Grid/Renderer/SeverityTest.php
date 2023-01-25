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

use Magento\AdminNotification\Block\Grid\Renderer\Severity;
use Magento\AdminNotification\Model\Inbox;
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Framework\DataObject;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SeverityTest extends TestCase
{
    /**
     * System under Test
     *
     * @var Severity
     */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var Inbox|MockObject $inboxMock */
        $inboxMock = $this->createMock(Inbox::class);

        /** @var Context|MockObject $contextMock */
        $contextMock = $this->createMock(Context::class);

        $this->sut = new Severity($contextMock, $inboxMock);
    }

    public function testShouldRenderSeverity() : void
    {
        /** @var Column|MockObject $columnMock */
        $columnMock = $this->getMockBuilder(Column::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIndex'])
            ->getMock();
        $columnMock->expects($this->exactly(5))->method('getIndex')->willReturn('index');
        $this->sut->setColumn($columnMock);
        $dataObject = new DataObject();

        // Test critical severity
        $dataObject->setData('index', 1);
        $actual = $this->sut->render($dataObject);
        $expected = '<span class="grid-severity-critical"><span></span></span>';

        $this->assertEquals($actual, $expected);

        // Test major severity
        $dataObject->setData('index', 2);
        $actual = $this->sut->render($dataObject);
        $expected = '<span class="grid-severity-major"><span></span></span>';

        $this->assertEquals($actual, $expected);

        // Test minor severity
        $dataObject->setData('index', 3);
        $actual = $this->sut->render($dataObject);
        $expected = '<span class="grid-severity-minor"><span></span></span>';

        $this->assertEquals($actual, $expected);

        // Test notice severity
        $dataObject->setData('index', 4);
        $actual = $this->sut->render($dataObject);
        $expected = '<span class="grid-severity-notice"><span></span></span>';

        $this->assertEquals($actual, $expected);

        // Test default severity
        $dataObject->setData('index', 5);
        $actual = $this->sut->render($dataObject);
        $expected = '<span class="grid-severity-"><span></span></span>';

        $this->assertEquals($actual, $expected);
    }
}
