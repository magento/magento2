<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Model\Indexer\Rule;

use ArrayIterator;
use Magento\CatalogRule\Model\Indexer\Rule\GetAffectedProductIds;
use Magento\CatalogRule\Model\ResourceModel\Rule as RuleResourceModel;
use Magento\CatalogRule\Model\ResourceModel\Rule\Collection;
use Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\CatalogRule\Model\Rule;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GetAffectedProductIdsTest extends TestCase
{
    /**
     * @var CollectionFactory|MockObject
     */
    private $ruleCollectionFactory;

    /**
     * @var RuleResourceModel|MockObject
     */
    private $ruleResourceModel;

    /**
     * @var GetAffectedProductIds
     */
    private $getAffectedProductIds;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->ruleCollectionFactory = $this->createMock(CollectionFactory::class);
        $this->ruleResourceModel = $this->createMock(RuleResourceModel::class);

        $this->getAffectedProductIds = new GetAffectedProductIds(
            $this->ruleCollectionFactory,
            $this->ruleResourceModel
        );
    }

    /**
     * @return void
     */
    public function testExecute(): void
    {
        $ruleIds = [1, 2, 5];
        $oldMatch = [3, 7, 9];
        $newMatch = [6];
        $this->ruleResourceModel->expects($this->once())
            ->method('getProductIdsByRuleIds')
            ->willReturn($oldMatch);

        $collection = $this->createMock(Collection::class);
        $rule = $this->createMock(Rule::class);
        $this->ruleCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($collection);
        $collection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('rule_id', ['in' => $ruleIds])
            ->willReturnSelf();
        $collection->expects($this->once())
            ->method('getIterator')
            ->willReturn(new ArrayIterator([$rule]));
        $rule->expects($this->once())
            ->method('getMatchingProductIds')
            ->willReturn(array_flip($newMatch));

        $this->assertEquals(array_merge($oldMatch, $newMatch), $this->getAffectedProductIds->execute($ruleIds));
    }
}
