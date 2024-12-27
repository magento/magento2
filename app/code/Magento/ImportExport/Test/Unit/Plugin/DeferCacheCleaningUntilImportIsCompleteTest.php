<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Plugin;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\RequestInterface;
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
     * @var TypeListInterface|MockObject
     */
    private $cacheTypeList;

    /**
     * @var RequestInterface|MockObject
     */
    private $request;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheCleaner = $this->getMockForAbstractClass(DeferredCacheCleanerInterface::class);
        $this->cacheTypeList = $this->getMockForAbstractClass(TypeListInterface::class);
        $this->request = $this->createMock(RequestInterface::class);
        $this->plugin = new DeferCacheCleaningUntilImportIsComplete(
            $this->cacheCleaner,
            $this->cacheTypeList,
            $this->request
        );
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
        // Assuming 'behavior' and 'entity' are the parameter names
        $this->request->expects($this->any())->method('getParam')->willReturnMap([
            ['behavior', null, 'add_update'],
            ['entity', null, 'customer'],
        ]);
        $result = $this->plugin->afterImportSource($subject, true);
        $this->assertTrue($result);
    }
}
