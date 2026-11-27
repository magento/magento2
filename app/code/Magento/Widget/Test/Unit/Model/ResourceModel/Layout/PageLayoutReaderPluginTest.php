<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Widget\Test\Unit\Model\ResourceModel\Layout;

use Magento\Framework\View\Layout\Reader\Context as ReaderContext;
use Magento\Framework\View\Page\Layout\Reader as PageLayoutReader;
use Magento\Widget\Model\ResourceModel\Layout\PageLayoutReaderPlugin;
use PHPUnit\Framework\TestCase;

class PageLayoutReaderPluginTest extends TestCase
{
    public function testIsReadingOnlyWhileReaderReads(): void
    {
        $plugin = new PageLayoutReaderPlugin();
        $readerContext = $this->createStub(ReaderContext::class);
        $readingDuringProceed = null;

        $this->assertFalse($plugin->isReading());
        $plugin->aroundRead(
            $this->createStub(PageLayoutReader::class),
            function ($context, $pageLayout) use ($plugin, $readerContext, &$readingDuringProceed) {
                $this->assertSame($readerContext, $context);
                $this->assertSame('1column', $pageLayout);
                $readingDuringProceed = $plugin->isReading();
            },
            $readerContext,
            '1column'
        );

        $this->assertTrue($readingDuringProceed);
        $this->assertFalse($plugin->isReading());
    }

    public function testIsReadingIsResetWhenReadFails(): void
    {
        $plugin = new PageLayoutReaderPlugin();
        try {
            $plugin->aroundRead(
                $this->createStub(PageLayoutReader::class),
                $this->failingRead(),
                $this->createStub(ReaderContext::class),
                '1column'
            );
            $this->fail('The read exception was swallowed');
        } catch (\RuntimeException $exception) {
            $this->assertSame('read failed', $exception->getMessage());
        }

        $this->assertFalse($plugin->isReading());
    }

    private function failingRead(): callable
    {
        return function () {
            throw new \RuntimeException('read failed');
        };
    }
}
