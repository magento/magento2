<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Unit\Plugin\Indexer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ImportExportTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Indexer processor mock
     *
     * @var \Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $ruleProductProcessor;

    /**
     * Import model mock
     *
     * @var \Magento\ImportExport\Model\Import|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $subject;

    /**
     * Tested plugin
     *
     * @var \Magento\CatalogRule\Plugin\Indexer\ImportExport
     */
    protected $plugin;

    protected function setUp(): void
    {
        $this->ruleProductProcessor = $this->createPartialMock(
            \Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor::class,
            ['isIndexerScheduled', 'markIndexerAsInvalid']
        );
        $this->ruleProductProcessor->expects($this->once())->method('isIndexerScheduled')->willReturn(false);
        $this->subject = $this->createMock(\Magento\ImportExport\Model\Import::class);

        $this->plugin = (new ObjectManager($this))->getObject(
            \Magento\CatalogRule\Plugin\Indexer\ImportExport::class,
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
