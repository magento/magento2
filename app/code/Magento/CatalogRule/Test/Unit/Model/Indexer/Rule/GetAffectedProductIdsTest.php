<?php
/************************************************************************
 *
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Model\Indexer\Rule;

use ArrayIterator;
use Magento\CatalogRule\Model\Indexer\Rule\GetAffectedProductIds;
use Magento\CatalogRule\Model\ResourceModel\Rule\Collection;
use Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\CatalogRule\Model\Rule;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
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
     * @var ResourceConnection|MockObject
     */
    private $resource;

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
        $this->resource = $this->createMock(ResourceConnection::class);

        $this->getAffectedProductIds = new GetAffectedProductIds(
            $this->ruleCollectionFactory,
            $this->resource
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
        $connection = $this->createMock(AdapterInterface::class);
        $select = $this->createMock(Select::class);
        $connection->expects($this->once())->method('fetchCol')->willReturn($oldMatch);
        $connection->expects($this->once())->method('select')->willReturn($select);
        $select->expects($this->once())->method('from')->willReturnSelf();
        $select->expects($this->once())
            ->method('where')
            ->with('t.rule_id IN (?)', $ruleIds)
            ->willReturnSelf();
        $select->expects($this->once())
            ->method('distinct')
            ->with(true)
            ->willReturnSelf();
        $this->resource->expects($this->once())->method('getConnection')->willReturn($connection);

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
