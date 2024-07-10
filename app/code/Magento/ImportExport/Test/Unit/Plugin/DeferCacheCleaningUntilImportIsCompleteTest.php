<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Plugin;

use Magento\Framework\Indexer\DeferredCacheCleanerInterface;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Plugin\DeferCacheCleaningUntilImportIsComplete;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeferCacheCleaningUntilImportIsCompleteTest extends TestCase
{
    /**
     * @var DeferCacheCleaningUntilImportIsComplete
     */
    private $plugin;

    /**
     * @var DeferredCacheCleanerInterface|MockObject
     */
    private $cacheCleaner;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheCleaner = $this->getMockForAbstractClass(DeferredCacheCleanerInterface::class);
        $this->plugin = new DeferCacheCleaningUntilImportIsComplete($this->cacheCleaner);
    }

    /**
     * @return void
     */
    public function testBeforeMethod()
    {
        $this->cacheCleaner->expects($this->once())->method('start');
        $subject = $this->createMock(Import::class);
        $this->plugin->beforeImportSource($subject);
    }

    /**
     * @return void
     */
    public function testAfterMethod()
    {
        $this->cacheCleaner->expects($this->once())->method('flush');
        $subject = $this->createMock(Import::class);
        $result = $this->plugin->afterImportSource($subject, true);
        $this->assertTrue($result);
    }
}
