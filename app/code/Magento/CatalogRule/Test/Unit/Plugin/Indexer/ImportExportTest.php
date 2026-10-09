<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);


namespace Magento\CatalogRule\Test\Unit\Plugin\Indexer;

use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor;
use Magento\CatalogRule\Plugin\Indexer\ImportExport;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\ImportExport\Model\Import;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class ImportExportTest extends TestCase
{
    /**
     * Indexer processor mock
     *
     * @var RuleProductProcessor|MockObject
     */
    protected $ruleProductProcessor;

    /**
     * Import model mock
     *
     * @var Import|Stub
     */
    protected $subject;

    /**
     * Tested plugin
     *
     * @var ImportExport
     */
    protected $plugin;

    protected function setUp(): void
    {
        $this->ruleProductProcessor = $this->createPartialMock(
            RuleProductProcessor::class,
            ['isIndexerScheduled', 'markIndexerAsInvalid']
        );
        $this->subject = $this->createStub(Import::class);

        $this->plugin = (new ObjectManager($this))->getObject(
            ImportExport::class,
            [
                'ruleProductProcessor' => $this->ruleProductProcessor,
            ]
        );
    }

    public function testAfterImportSource()
    {
        $result = true;

        $this->subject->method('getEntity')->willReturn(Product::ENTITY);
        $this->ruleProductProcessor->expects($this->once())
            ->method('isIndexerScheduled')
            ->willReturn(false);
        $this->ruleProductProcessor->expects($this->once())
            ->method('markIndexerAsInvalid');

        $this->assertEquals($result, $this->plugin->afterImportSource($this->subject, $result));
    }

    public function testAfterImportSourceWithScheduledIndexer()
    {
        $result = true;

        $this->subject->method('getEntity')->willReturn(Product::ENTITY);
        $this->ruleProductProcessor->expects($this->once())
            ->method('isIndexerScheduled')
            ->willReturn(true);
        $this->ruleProductProcessor->expects($this->never())
            ->method('markIndexerAsInvalid');

        $this->assertEquals($result, $this->plugin->afterImportSource($this->subject, $result));
    }

    public function testAfterImportSourceWithNonProductEntity()
    {
        $result = true;

        $this->subject->method('getEntity')->willReturn('customer');
        $this->ruleProductProcessor->expects($this->never())
            ->method('isIndexerScheduled');
        $this->ruleProductProcessor->expects($this->never())
            ->method('markIndexerAsInvalid');

        $this->assertEquals($result, $this->plugin->afterImportSource($this->subject, $result));
    }
}
