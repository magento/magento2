<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Test\Unit\ViewModel;

use Magento\NewRelicReporting\Model\NewRelicWrapper;
use Magento\NewRelicReporting\ViewModel\BrowserMonitoringFooterJs;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for BrowserMonitoringFooterJs ViewModel
 *
 * @covers \Magento\NewRelicReporting\ViewModel\BrowserMonitoringFooterJs
 */
class BrowserMonitoringFooterJsTest extends TestCase
{
    /**
     * @var BrowserMonitoringFooterJs
     */
    private BrowserMonitoringFooterJs $viewModel;

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
        $this->viewModel = new BrowserMonitoringFooterJs($this->newRelicWrapperMock);
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
                '<script type="text/x-magento-init">{"*":{"newRelicFooter":{"enabled":true}}}</script>',
                '<script type="text/x-magento-init">{"*":{"newRelicFooter":{"enabled":true}}}</script>'
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
     * @param string|null $footerContent
     * @param string|null $expected
     * @return void
     * @dataProvider getContentDataProvider
     */
    public function testGetContent(bool $isEnabled, ?string $footerContent, ?string $expected): void
    {
        $this->newRelicWrapperMock->method('isAutoInstrumentEnabled')->willReturn($isEnabled);

        if ($isEnabled) {
            $this->newRelicWrapperMock->method('getBrowserTimingFooter')
                ->with(false)->willReturn($footerContent);
        }

        $this->assertEquals($expected, $this->viewModel->getContent());
    }

    /**
     * Test that getBrowserTimingFooter is called with correct parameter
     *
     * @return void
     */
    public function testGetContentCallsBrowserTimingFooterWithCorrectParameter(): void
    {
        $this->newRelicWrapperMock->expects($this->once())
            ->method('isAutoInstrumentEnabled')
            ->willReturn(true);

        $this->newRelicWrapperMock->expects($this->once())
            ->method('getBrowserTimingFooter')
            ->with($this->identicalTo(false))
            ->willReturn('<script type="text/x-magento-init">{"*":{"newRelicFooter":{"enabled":true}}}</script>');

        $this->viewModel->getContent();
    }
}
