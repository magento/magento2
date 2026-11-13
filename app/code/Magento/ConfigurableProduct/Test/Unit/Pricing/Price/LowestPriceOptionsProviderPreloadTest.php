<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\LinkedProductSelectBuilderInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Pricing\Price\LowestPriceOptionsProvider;
use Magento\Customer\Model\Group\RetrieverInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LowestPriceOptionsProviderPreloadTest extends TestCase
{
    /**
     * @var AdapterInterface|MockObject
     */
    private $connection;

    /**
     * @var LinkedProductSelectBuilderInterface|MockObject
     */
    private $linkedProductSelectBuilder;

    /**
     * @var CollectionFactory|MockObject
     */
    private $collectionFactory;

    /**
     * @var Collection|MockObject
     */
    private $collection;

    /**
     * @var RetrieverInterface|MockObject
     */
    private $customerGroupRetriever;

    /**
     * @var LowestPriceOptionsProvider
     */
    private $model;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(AdapterInterface::class);
        $resourceConnection = $this->createStub(ResourceConnection::class);
        $resourceConnection->method('getConnection')->willReturn($this->connection);
        $this->linkedProductSelectBuilder = $this->createMock(LinkedProductSelectBuilderInterface::class);
        $this->collectionFactory = $this->createMock(CollectionFactory::class);
        $this->customerGroupRetriever = $this->createStub(RetrieverInterface::class);

        $this->model = new LowestPriceOptionsProvider(
            $resourceConnection,
            $this->linkedProductSelectBuilder,
            $this->collectionFactory,
            $this->createStub(StoreManagerInterface::class),
            $this->customerGroupRetriever
        );
    }

    public function testPreloadResolvesAllParentsWithOneQueryAndOneCollection(): void
    {
        $firstSelect = $this->linkedSelect('SELECT first');
        $secondSelect = $this->linkedSelect('SELECT second');
        $this->linkedProductSelectBuilder->expects($this->exactly(2))
            ->method('build')
            ->willReturnMap([[10, 1, [$firstSelect]], [20, 1, [$secondSelect]]]);
        $this->connection->expects($this->once())
            ->method('fetchAll')
            ->with('(SELECT first) UNION (SELECT second)', [], \Zend_Db::FETCH_NUM)
            ->willReturn([['11', null, '0'], ['21', null, '1'], ['22', null, '1'], ['22', '5.0000', '1']]);
        $this->connection->expects($this->never())->method('fetchCol');

        $children = [11 => $this->product(11), 21 => $this->product(21), 22 => $this->product(22)];
        $this->collection = $this->createCollection();
        $this->collection->expects($this->once())->method('addIdFilter')->with([11, 21, 22])->willReturnSelf();
        $this->collection->method('getItems')->willReturn($children);
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($this->collection);

        $firstParent = $this->product(10, Configurable::TYPE_CODE);
        $secondParent = $this->product(20, Configurable::TYPE_CODE);
        $this->model->preloadProducts([$firstParent, $this->product(30), $secondParent]);

        $this->assertSame([11 => $children[11]], $this->model->getProducts($firstParent));
        $this->assertSame([21 => $children[21], 22 => $children[22]], $this->model->getProducts($secondParent));
    }

    public function testPreloadStoresEmptyResultForParentWithoutLinkedProducts(): void
    {
        $this->linkedProductSelectBuilder->expects($this->exactly(2))->method('build')->willReturn([]);
        $this->connection->expects($this->never())->method('fetchAll');
        $this->connection->expects($this->never())->method('fetchCol');
        $this->collection = $this->createCollection();
        $this->collection->expects($this->once())->method('addIdFilter')->with([])->willReturnSelf();
        $this->collection->method('getItems')->willReturn([]);
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($this->collection);

        $firstParent = $this->product(10, Configurable::TYPE_CODE);
        $secondParent = $this->product(20, Configurable::TYPE_CODE);
        $this->model->preloadProducts([$firstParent, $secondParent]);

        $this->assertSame([], $this->model->getProducts($firstParent));
        $this->assertSame([], $this->model->getProducts($secondParent));
    }

    public function testPreloadResolvesOnlyTheChunkHoldingTheRequestedProduct(): void
    {
        $parents = [];
        for ($id = 1; $id <= 101; $id++) {
            $parents[] = $this->product($id, Configurable::TYPE_CODE);
        }
        $select = $this->createStub(Select::class);
        $select->method('columns')->willReturnSelf();
        $select->method('__toString')->willReturn('SELECT last');
        $this->linkedProductSelectBuilder->expects($this->once())
            ->method('build')
            ->with(101, 1)
            ->willReturn([$select]);
        $this->connection->expects($this->once())
            ->method('fetchAll')
            ->with('(SELECT last)', [], \Zend_Db::FETCH_NUM)
            ->willReturn([['201', '0']]);
        $child = $this->product(201);
        $this->collection = $this->createCollection();
        $this->collection->expects($this->once())->method('addIdFilter')->with([201])->willReturnSelf();
        $this->collection->method('getItems')->willReturn([201 => $child]);
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($this->collection);

        $this->model->preloadProducts($parents);

        $this->assertSame([201 => $child], $this->model->getProducts($parents[100]));
    }

    public function testPreloadIsIgnoredForAnotherCustomerGroup(): void
    {
        $this->customerGroupRetriever->method('getCustomerGroupId')->willReturnOnConsecutiveCalls(0, 1);
        $select = $this->createStub(Select::class);
        $select->method('__toString')->willReturn('SELECT single');
        $this->linkedProductSelectBuilder->expects($this->once())->method('build')->with(10, 1)->willReturn([$select]);
        $this->connection->expects($this->never())->method('fetchAll');
        $this->connection->expects($this->once())->method('fetchCol')->with('(SELECT single)')->willReturn(['11']);
        $child = $this->product(11);
        $this->collection = $this->createCollection();
        $this->collection->expects($this->once())->method('addIdFilter')->with(['11'])->willReturnSelf();
        $this->collection->method('getItems')->willReturn([11 => $child]);
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($this->collection);

        $parent = $this->product(10, Configurable::TYPE_CODE);
        $this->model->preloadProducts([$parent, $this->product(20, Configurable::TYPE_CODE)]);

        $this->assertSame([11 => $child], $this->model->getProducts($parent));
    }

    public function testPreloadRunsNoQueryUntilLinkedProductsAreRequested(): void
    {
        $this->linkedProductSelectBuilder->expects($this->never())->method('build');
        $this->connection->expects($this->never())->method('fetchAll');
        $this->collectionFactory->expects($this->never())->method('create');

        $this->model->preloadProducts(
            [$this->product(10, Configurable::TYPE_CODE), $this->product(20, Configurable::TYPE_CODE)]
        );
    }

    public function testPreloadSkipsSingleConfigurableProduct(): void
    {
        $select = $this->createStub(Select::class);
        $select->method('__toString')->willReturn('SELECT single');
        $this->linkedProductSelectBuilder->expects($this->once())->method('build')->willReturn([$select]);
        $this->connection->expects($this->never())->method('fetchAll');
        $this->connection->expects($this->once())->method('fetchCol')->with('(SELECT single)')->willReturn(['11']);
        $child = $this->product(11);
        $this->collection = $this->createCollection();
        $this->collection->expects($this->once())->method('addIdFilter')->with(['11'])->willReturnSelf();
        $this->collection->method('getItems')->willReturn([11 => $child]);
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($this->collection);

        $parent = $this->product(10, Configurable::TYPE_CODE);
        $this->model->preloadProducts([$parent, $this->product(30)]);

        $this->assertSame([11 => $child], $this->model->getProducts($parent));
    }

    private function linkedSelect(string $sql): Select&MockObject
    {
        $select = $this->createMock(Select::class);
        $select->expects($this->once())
            ->method('columns')
            ->with($this->callback(fn (array $columns) => array_keys($columns) === ['parent_index']))
            ->willReturnSelf();
        $select->method('__toString')->willReturn($sql);

        return $select;
    }

    private function createCollection(): Collection&MockObject
    {
        $collection = $this->createPartialMock(Collection::class, ['addAttributeToSelect', 'addIdFilter', 'getItems']);
        $collection->expects($this->once())
            ->method('addAttributeToSelect')
            ->with(['price', 'special_price', 'special_from_date', 'special_to_date', 'tax_class_id'])
            ->willReturnSelf();

        return $collection;
    }

    private function product(int $id, string $typeId = 'simple'): Product
    {
        $product = $this->createStub(Product::class);
        $product->method('getId')->willReturn($id);
        $product->method('getStoreId')->willReturn(1);
        $product->method('getTypeId')->willReturn($typeId);

        return $product;
    }
}
