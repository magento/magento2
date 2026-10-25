<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Test\Unit\ViewModel;

use Magento\NewRelicReporting\Model\NewRelicWrapper;
use Magento\NewRelicReporting\ViewModel\BrowserMonitoringHeaderJs;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Test for BrowserMonitoringHeaderJs ViewModel
 *
 * @covers \Magento\NewRelicReporting\ViewModel\BrowserMonitoringHeaderJs
 */
class BrowserMonitoringHeaderJsTest extends TestCase
{
    /**
     * @var BrowserMonitoringHeaderJs
     */
    private BrowserMonitoringHeaderJs $viewModel;

    /**
     * @var NewRelicWrapper|MockObject
     */
    private NewRelicWrapper|MockObject $newRelicWrapperMock;

    /**
     * Set up test dependencies
     *
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->newRelicWrapperMock = $this->createMock(NewRelicWrapper::class);
        $this->viewModel = new BrowserMonitoringHeaderJs($this->newRelicWrapperMock);
    }

    /**
     * Data provider for getContent scenarios
     *
     * @return array
     */
    public static function getContentDataProvider(): array
    {
        return [
            'enabled_with_content' => [true,
                '<script type="text/x-magento-init">{"*":{"newRelicHeader":{"enabled":true}}}</script>',
                '<script type="text/x-magento-init">{"*":{"newRelicHeader":{"enabled":true}}}</script>'
            ],
            'enabled_with_null' => [true, null, null],
            'enabled_with_empty' => [true, '', ''],
            'disabled' => [false, null, null]
        ];
    }

    /**
     * Test getContent method with various scenarios
     *
     * @param bool $isEnabled
     * @param string|null $headerContent
     * @param string|null $expected
     * @return void
     */
    #[DataProvider('getContentDataProvider')]
    public function testGetContent(bool $isEnabled, ?string $headerContent, ?string $expected): void
    {
        $this->newRelicWrapperMock->method('isAutoInstrumentEnabled')->willReturn($isEnabled);

        if ($isEnabled) {
            $this->newRelicWrapperMock->method('getBrowserTimingHeader')
                ->with(false)->willReturn($headerContent);
        }

        $this->assertEquals($expected, $this->viewModel->getContent());
    }

    /**
     * Test that getBrowserTimingFooter is called with correct parameter
     *
     * @return void
     */
    public function testGetContentCallsBrowserTimingHeaderWithCorrectParameter(): void
    {
        $this->newRelicWrapperMock->expects($this->once())
            ->method('isAutoInstrumentEnabled')
            ->willReturn(true);

        $this->newRelicWrapperMock->expects($this->once())
            ->method('getBrowserTimingHeader')
            ->with($this->identicalTo(false))
            ->willReturn('<script type="text/x-magento-init">{"*":{"newRelicHeader":{"enabled":true}}}</script>');

        $this->viewModel->getContent();
    }
}
