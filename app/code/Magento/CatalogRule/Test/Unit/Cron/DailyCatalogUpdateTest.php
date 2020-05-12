<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace Magento\CatalogRule\Test\Unit\Cron;

use Magento\CatalogRule\Cron\DailyCatalogUpdate;
use Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DailyCatalogUpdateTest extends TestCase
{
    /**
     * Processor
     *
     * @var RuleProductProcessor|MockObject
     */
    protected $ruleProductProcessor;

    /**
     * Cron object
     *
     * @var DailyCatalogUpdate
     */
    protected $cron;

    protected function setUp(): void
    {
        $this->ruleProductProcessor = $this->createMock(
            RuleProductProcessor::class
        );

        $this->cron = (new ObjectManager($this))->getObject(
            DailyCatalogUpdate::class,
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
