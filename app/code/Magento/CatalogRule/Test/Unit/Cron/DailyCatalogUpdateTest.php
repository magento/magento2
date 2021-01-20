<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Unit\Cron;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DailyCatalogUpdateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Processor
     *
     * @var \Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $ruleProductProcessor;

    /**
     * Cron object
     *
     * @var \Magento\CatalogRule\Cron\DailyCatalogUpdate
     */
    protected $cron;

    protected function setUp(): void
    {
        $this->ruleProductProcessor = $this->createMock(
            \Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor::class
        );

        $this->cron = (new ObjectManager($this))->getObject(
            \Magento\CatalogRule\Cron\DailyCatalogUpdate::class,
            [
                'ruleProductProcessor' => $this->ruleProductProcessor,
            ]
        );
    }

    public function testDailyCatalogUpdate()
    {
        $this->ruleProductProcessor->expects($this->once())->method('markIndexerAsInvalid');

        $this->cron->execute();
    }
}
