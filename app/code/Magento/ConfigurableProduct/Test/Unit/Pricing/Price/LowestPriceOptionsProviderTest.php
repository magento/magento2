<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Unit\Pricing\Price;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\LinkedProductSelectBuilderInterface;

class LowestPriceOptionsProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ConfigurableProduct\Pricing\Price\LowestPriceOptionsProvider
     */
    private $model;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnection;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connection;

    /**
     * @var LinkedProductSelectBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $linkedProductSelectBuilder;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productCollection;

    protected function setUp()
    {
        $this->connection = $this
            ->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->getMock();
        $this->resourceConnection = $this
            ->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConnection'])
            ->getMock();
        $this->resourceConnection->expects($this->once())->method('getConnection')->willReturn($this->connection);
        $this->linkedProductSelectBuilder = $this
            ->getMockBuilder(LinkedProductSelectBuilderInterface::class)
            ->setMethods(['build'])
            ->getMock();
        $this->productCollection = $this
            ->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['addAttributeToSelect', 'addIdFilter', 'getItems'])
            ->getMock();
        $this->collectionFactory = $this
            ->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($this->productCollection);

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\ConfigurableProduct\Pricing\Price\LowestPriceOptionsProvider::class,
            [
                'resourceConnection' => $this->resourceConnection,
                'linkedProductSelectBuilder' => $this->linkedProductSelectBuilder,
                'collectionFactory' => $this->collectionFactory,
            ]
        );
    }

    public function testGetProducts()
    {
        $productId = 1;
        $linkedProducts = ['some', 'linked', 'products', 'dataobjects'];
        $product = $this->getMockBuilder(ProductInterface::class)->disableOriginalConstructor()->getMock();
        $product->expects($this->any())->method('getId')->willReturn($productId);
        $this->linkedProductSelectBuilder->expects($this->any())->method('build')->with($productId)->willReturn([]);
        $this->productCollection
            ->expects($this->once())
            ->method('addAttributeToSelect')
            ->with(['price', 'special_price', 'special_from_date', 'special_to_date', 'tax_class_id'])
            ->willReturnSelf();
        $this->productCollection->expects($this->once())->method('addIdFilter')->willReturnSelf();
        $this->productCollection->expects($this->once())->method('getItems')->willReturn($linkedProducts);

        $this->assertEquals($linkedProducts, $this->model->getProducts($product));
    }
}
