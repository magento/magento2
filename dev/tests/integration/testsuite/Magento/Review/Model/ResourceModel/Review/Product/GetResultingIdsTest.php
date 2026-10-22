<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Review\Model\ResourceModel\Review\Product;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Review\Model\Review;
use Magento\Review\Test\Fixture\Review as ReviewFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

#[DbIsolation(true)]
#[DataFixture(ProductFixture::class, ['name' => 'Zulu product', 'sku' => 'zulu-product'], 'product')]
#[DataFixture(ProductFixture::class, ['name' => 'Alpha product', 'sku' => 'alpha-product'], 'other_product')]
#[DataFixture(ReviewFixture::class, ['entity_pk_value' => '$product.id$'], 'first')]
#[DataFixture(ReviewFixture::class, ['entity_pk_value' => '$product.id$'], 'second')]
#[DataFixture(ReviewFixture::class, [
    'entity_pk_value' => '$product.id$',
    'status_id' => Review::STATUS_PENDING
], 'pending')]
#[DataFixture(ReviewFixture::class, ['entity_pk_value' => '$other_product.id$'], 'other_review')]
class GetResultingIdsTest extends TestCase
{
    public function testFilteredIdsUseNarrowProjection(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $collection = Bootstrap::getObjectManager()->create(Collection::class);
        $collection->addAttributeToFilter('sku', ['eq' => $fixtures->get('product')->getSku()]);
        $collection->addAttributeToFilter('rt.status_id', ['eq' => Review::STATUS_APPROVED]);
        $collection->addAttributeToFilter('rdt.title', ['like' => 'Review%']);
        $collection->setOrder('rt.review_id', 'DESC');
        $select = $collection->getSelect();
        $select->limit(1, 1);
        $originalSql = (string)$select;
        $connection = $collection->getConnection();
        $idsSelect = null;
        $adapter = $this->createStub(Mysql::class);
        foreach (['fetchAll', 'fetchCol'] as $method) {
            $adapter->method($method)->willReturnCallback(
                function (Select $query) use ($connection, $method, &$idsSelect): array {
                    $idsSelect = $query;
                    return $connection->$method($query);
                }
            );
        }
        $subject = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSelect', 'getConnection'])
            ->getMock();
        $subject->expects(self::once())->method('getSelect')->willReturn($select);
        $subject->expects(self::once())->method('getConnection')->willReturn($adapter);
        $expected = [(int)$fixtures->get('first')->getId(), (int)$fixtures->get('second')->getId()];
        rsort($expected, SORT_NUMERIC);

        self::assertSame($expected, array_map('intval', $subject->getResultingIds()));
        self::assertInstanceOf(Select::class, $idsSelect);
        self::assertSame(
            [['rt', 'review_id', null], ['rt', 'created_at', 'review_created_at']],
            $idsSelect->getPart(Select::COLUMNS)
        );
        self::assertNull($idsSelect->getPart(Select::LIMIT_COUNT));
        self::assertNull($idsSelect->getPart(Select::LIMIT_OFFSET));
        foreach ([Select::FROM, Select::WHERE, Select::ORDER] as $part) {
            self::assertSame($select->getPart($part), $idsSelect->getPart($part));
        }
        self::assertSame($originalSql, (string)$select);
    }

    public function testIdsSortedByNameWithProductAttributeFilter(): void
    {
        $this->assertProductSortedIds('name');
    }

    public function testIdsSortedBySkuWithProductAttributeFilter(): void
    {
        $this->assertProductSortedIds('sku');
    }

    public function testIdsSortedBySelectedNameAliasWithProductAttributeFilter(): void
    {
        $this->assertProductSortedIds('name', true);
    }

    private function assertProductSortedIds(string $attribute, bool $joinName = false): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $collection = Bootstrap::getObjectManager()->create(Collection::class);
        if ($joinName) {
            $collection->joinAttribute('name', 'catalog_product/name', 'entity_id');
            $collection->getSelect()->order('name ASC');
        }
        $collection->setOrder($attribute, 'ASC');
        $filterAttribute = $attribute === 'name' ? 'sku' : 'name';
        $collection->addAttributeToFilter($filterAttribute, ['in' => [
            $fixtures->get('product')->getData($filterAttribute),
            $fixtures->get('other_product')->getData($filterAttribute)
        ]]);
        $collection->addAttributeToFilter('rt.status_id', ['eq' => Review::STATUS_APPROVED]);
        $collection->setPageSize(1)->setCurPage(2)->load();
        self::assertCount(1, $collection->getItems());
        $collection->setOrder('rt.review_id', 'ASC');
        $select = $collection->getSelect();
        $originalSql = (string)$select;
        if ($attribute === 'name') {
            self::assertSame('name', $select->getPart(Select::ORDER)[0][0]);
        } else {
            self::assertSame('e.sku', $select->getPart(Select::ORDER)[0][0]);
        }
        $productReviewIds = [(int)$fixtures->get('first')->getId(), (int)$fixtures->get('second')->getId()];
        sort($productReviewIds, SORT_NUMERIC);
        $expected = array_merge([(int)$fixtures->get('other_review')->getId()], $productReviewIds);

        self::assertSame($expected, array_map('intval', $collection->getResultingIds()));
        self::assertSame($originalSql, (string)$select);
    }

    public function testIdsWithHavingOnColumnAlias(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $collection = Bootstrap::getObjectManager()->create(Collection::class);
        $collection->addAttributeToFilter('sku', ['eq' => $fixtures->get('product')->getSku()]);
        $collection->addAttributeToFilter('rt.status_id', ['eq' => Review::STATUS_APPROVED]);
        $collection->getSelect()->columns(['review_title' => 'rdt.title'])
            ->having('review_title = ?', $fixtures->get('first')->getTitle())
            ->having('nickname = ?', $fixtures->get('first')->getNickname())
            ->having('sku = ?', $fixtures->get('product')->getSku());
        $collection->setOrder('rt.review_id', 'ASC');
        $select = $collection->getSelect();
        $select->limit(1, 1);
        $originalSql = (string)$select;
        $expected = [(int)$fixtures->get('first')->getId(), (int)$fixtures->get('second')->getId()];
        sort($expected, SORT_NUMERIC);

        self::assertSame($expected, array_map('intval', $collection->getResultingIds()));
        self::assertSame($originalSql, (string)$select);
    }
}
