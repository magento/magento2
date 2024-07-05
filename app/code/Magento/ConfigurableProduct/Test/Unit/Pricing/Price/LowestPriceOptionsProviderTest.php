<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\LinkedProductSelectBuilderInterface;
use Magento\ConfigurableProduct\Pricing\Price\LowestPriceOptionsProvider;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LowestPriceOptionsProviderTest extends TestCase
{
    /**
     * @var LowestPriceOptionsProvider
     */
    private $model;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnection;

    /**
     * @var AdapterInterface|MockObject
     */
    protected $connection;

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
    private $productCollection;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var StoreInterface|MockObject
     */
    private $storeMock;

    protected function setUp(): void
    {
        $this->connection = $this
            ->getMockBuilder(AdapterInterface::class)
            ->getMock();
        $this->resourceConnection = $this
            ->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConnection'])
            ->getMock();
        $this->resourceConnection->expects($this->once())->method('getConnection')->willReturn($this->connection);
        $this->linkedProductSelectBuilder = $this
            ->getMockBuilder(LinkedProductSelectBuilderInterface::class)
            ->onlyMethods(['build'])
            ->getMockForAbstractClass();
        $this->productCollection = $this
            ->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addAttributeToSelect', 'addIdFilter', 'getItems'])
            ->getMock();
        $this->collectionFactory = $this
            ->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($this->productCollection);
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->onlyMethods(['getId'])
            ->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            LowestPriceOptionsProvider::class,
            [
                'resourceConnection' => $this->resourceConnection,
                'linkedProductSelectBuilder' => $this->linkedProductSelectBuilder,
                'collectionFactory' => $this->collectionFactory,
                'storeManager' => $this->storeManagerMock,
            ]
        );
    }

    public function testGetProducts()
    {
        $productId = 1;
        $storeId = 1;
        $linkedProducts = ['some', 'linked', 'products', 'dataobjects'];
        $product = $this->createMock(Product::class);
        $product->expects($this->any())->method('getId')->willReturn($productId);
        $product->expects($this->any())->method('getStoreId')->willReturn($storeId);
        $this->linkedProductSelectBuilder->expects($this->any())->method('build')->with($productId)->willReturn([]);
        $this->productCollection
            ->expects($this->once())
            ->method('addAttributeToSelect')
            ->with(['price', 'special_price', 'special_from_date', 'special_to_date', 'tax_class_id'])
            ->willReturnSelf();
        $this->productCollection->expects($this->once())->method('addIdFilter')->willReturnSelf();
        $this->productCollection->expects($this->once())->method('getItems')->willReturn($linkedProducts);
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->with(Store::DEFAULT_STORE_ID)
            ->willReturn($this->storeMock);
        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn(Store::DEFAULT_STORE_ID);

        $this->assertEquals($linkedProducts, $this->model->getProducts($product));
    }
}
