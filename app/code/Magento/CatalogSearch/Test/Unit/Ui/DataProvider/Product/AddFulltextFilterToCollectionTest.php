<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Ui\DataProvider\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\CatalogSearch\Model\ResourceModel\Search\Collection as SearchCollection;
use Magento\CatalogSearch\Ui\DataProvider\Product\AddFulltextFilterToCollection;
use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddFulltextFilterToCollectionTest extends TestCase
{
    /**
     * @var SearchCollection|MockObject
     */
    private $searchCollection;

    /**
     * @var ProductCollection|MockObject
     */
    private $collection;

    /**
     * @var Select|MockObject
     */
    private $select;

    /**
     * @var AddFulltextFilterToCollection
     */
    private $model;

    protected function setUp(): void
    {
        $entity = $this->createMock(AbstractEntity::class);
        $entity->method('getLinkField')->willReturn('entity_id');

        $this->searchCollection = $this->createPartialMock(
            SearchCollection::class,
            ['getEntity', 'getBackendSearchEntityIdsSelect']
        );
        $this->searchCollection->method('getEntity')->willReturn($entity);

        $this->select = $this->createMock(Select::class);
        $this->collection = $this->createMock(ProductCollection::class);
        $this->collection->method('getSelect')->willReturn($this->select);

        $this->model = new AddFulltextFilterToCollection($this->searchCollection);
    }

    public function testAddFilterJoinsSearchResultSelect()
    {
        $idsSelect = $this->createMock(Select::class);
        $this->searchCollection->expects($this->once())
            ->method('getBackendSearchEntityIdsSelect')
            ->with('test')
            ->willReturn($idsSelect);

        $this->select->expects($this->once())
            ->method('joinInner')
            ->with(
                ['search_result' => $idsSelect],
                'search_result.entity_id = e.entity_id',
                []
            );

        $this->model->addFilter($this->collection, 'fulltext', ['fulltext' => 'test']);
    }

    /**
     * @param array|null $condition
     */
    #[DataProvider('emptyConditionDataProvider')]
    public function testAddFilterIsSkippedForEmptyCondition($condition)
    {
        $this->searchCollection->expects($this->never())
            ->method('getBackendSearchEntityIdsSelect');
        $this->select->expects($this->never())
            ->method('joinInner');

        $this->model->addFilter($this->collection, 'fulltext', $condition);
    }

    /**
     * @return array
     */
    public static function emptyConditionDataProvider(): array
    {
        return [
            'no condition' => [null],
            'no fulltext key' => [['like' => 'test']],
            'empty fulltext' => [['fulltext' => '']],
        ];
    }
}
