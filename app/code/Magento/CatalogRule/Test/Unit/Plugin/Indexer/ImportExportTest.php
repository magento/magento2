<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace Magento\CatalogRule\Test\Unit\Plugin\Indexer;

use Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor;
use Magento\CatalogRule\Plugin\Indexer\ImportExport;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\ImportExport\Model\Import;
use PHPUnit\Framework\MockObject\MockObject;
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
     * @var \Magento\ImportExport\Model\Import|MockObject
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
        $this->ruleProductProcessor->expects($this->once())->method('isIndexerScheduled')->willReturn(false);
        $this->subject = $this->createMock(Import::class);

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

        $this->ruleProductProcessor->expects($this->once())
            ->method('markIndexerAsInvalid');

        $this->assertEquals($result, $this->plugin->afterImportSource($this->subject, $result));
    }
}
