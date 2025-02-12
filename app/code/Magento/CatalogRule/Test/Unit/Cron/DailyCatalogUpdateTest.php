<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Cron;

use Magento\CatalogRule\Cron\DailyCatalogUpdate;
use Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor;
use Magento\CatalogRule\Model\ResourceModel\Rule\Collection as RuleCollection;
use Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DailyCatalogUpdateTest extends TestCase
{
    /**
     * @var RuleProductProcessor|MockObject
     */
    private $ruleProductProcessor;

    /**
     * @var RuleCollectionFactory|MockObject
     */
    private $ruleCollectionFactory;

    /**
     * @var DailyCatalogUpdate
     */
    private $cron;

    protected function setUp(): void
    {
        $this->ruleProductProcessor = $this->createMock(RuleProductProcessor::class);
        $this->ruleCollectionFactory = $this->createMock(RuleCollectionFactory::class);

        $this->cron = new DailyCatalogUpdate($this->ruleProductProcessor, $this->ruleCollectionFactory);
    }

    /**
     * @dataProvider executeDataProvider
     * @param int $activeRulesCount
     * @param bool $isInvalidationNeeded
     */
    public function testExecute(int $activeRulesCount, bool $isInvalidationNeeded)
    {
        $ruleCollection = $this->createMock(RuleCollection::class);
        $this->ruleCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($ruleCollection);
        $ruleCollection->expects($this->once())
            ->method('addIsActiveFilter')
            ->willReturn($ruleCollection);
        $ruleCollection->expects($this->once())
            ->method('getSize')
            ->willReturn($activeRulesCount);
        $this->ruleProductProcessor->expects($isInvalidationNeeded ? $this->once() : $this->never())
            ->method('markIndexerAsInvalid');

        $this->cron->execute();
    }

    /**
     * @return array
     */
    public static function executeDataProvider(): array
    {
        return [
            [2, true],
            [0, false],
        ];
    }
}
