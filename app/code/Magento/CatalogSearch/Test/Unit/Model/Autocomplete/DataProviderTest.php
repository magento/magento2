<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Autocomplete;

use Magento\CatalogSearch\Model\Autocomplete\DataProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Search\Model\Autocomplete\Item;
use Magento\Search\Model\Autocomplete\ItemFactory;
use Magento\Search\Model\Query;
use Magento\Search\Model\QueryFactory;
use Magento\Search\Model\ResourceModel\Query\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataProviderTest extends TestCase
{
    /**
     * @var DataProvider
     */
    private $model;

    /**
     * @var Query|MockObject
     */
    private $query;

    /**
     * @var ItemFactory|MockObject
     */
    private $itemFactory;

    /**
     * @var Collection|MockObject
     */
    private $suggestCollection;

    /**
     * @var integer
     */
    private $limit = 3;

    protected function setUp(): void
    {
        $helper = new ObjectManagerHelper($this);

        $this->suggestCollection = $this->createPartialMock(
            Collection::class,
            ['getIterator']
        );

        $this->query = $this->createPartialMock(
            Query::class,
            ['getQueryText', 'getSuggestCollection']
        );
        $this->query->expects($this->any())
            ->method('getSuggestCollection')
            ->willReturn($this->suggestCollection);

        $queryFactory = $this->createPartialMock(
            QueryFactory::class,
            ['get']
        );
        $queryFactory->expects($this->any())
            ->method('get')
            ->willReturn($this->query);

        $this->itemFactory = $this->createPartialMock(
            ItemFactory::class,
            ['create']
        );

        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->expects($this->any())
            ->method('getValue')
            ->willReturn($this->limit);

        $this->model = $helper->getObject(
            DataProvider::class,
            [
                'queryFactory' => $queryFactory,
                'itemFactory' => $this->itemFactory,
                'scopeConfig' => $scopeConfig
            ]
        );
    }

    public function testGetItems()
    {
        $queryString = 'string';
        $expected = ['title' => $queryString, 'num_results' => 100500];
        $collection = [
            ['query_text' => 'string1', 'num_results' => 1],
            ['query_text' => 'string2', 'num_results' => 2],
            ['query_text' => 'string11', 'num_results' => 11],
            ['query_text' => 'string100', 'num_results' => 100],
            ['query_text' => $queryString, 'num_results' => 100500]
        ];
        $this->buildCollection($collection);
        $this->query->expects($this->once())
            ->method('getQueryText')
            ->willReturn($queryString);

        $itemMock = $this->createPartialMock(
            Item::class,
            ['getTitle', 'toArray']
        );
        
        $callCount = 0;
        $titles = [$queryString, 'string1', 'string2', 'string11', 'string100'];
        $itemMock->expects($this->any())
            ->method('getTitle')
            ->willReturnCallback(function () use (&$callCount, $titles) {
                return $titles[$callCount++] ?? null;
            });
        $itemMock->expects($this->any())
            ->method('toArray')
            ->willReturn($expected);

        $this->itemFactory->expects($this->any())->method('create')->willReturn($itemMock);

        $result = $this->model->getItems();
        $this->assertEquals($expected, $result[0]->toArray());
        $this->assertCount($this->limit, $result);
    }

    /**
     * @param array $data
     */
    private function buildCollection(array $data)
    {
        $collectionData = [];
        foreach ($data as $collectionItem) {
            $collectionData[] = new DataObject($collectionItem);
        }
        $this->suggestCollection->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($collectionData));
    }

    public function testGetItemsWithEmptyQueryText()
    {
        $this->query->expects($this->once())
            ->method('getQueryText')
            ->willReturn('');
        $this->query->expects($this->never())
            ->method('getSuggestCollection');
        $this->itemFactory->expects($this->never())
            ->method('create');
        $result = $this->model->getItems();
        $this->assertEmpty($result);
    }
}
