<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model\Plugin\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\GetStoreSpecificProductChildIds;
use Magento\ConfigurableProduct\Plugin\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider\GetProductChildIds;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetProductChildIdsTest extends TestCase
{
    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var GetStoreSpecificProductChildIds|MockObject
     */
    private $getChildProductFromStoreIdMock;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    private $productRepositoryMock;

    /**
     * @var GetProductChildIds
     */
    private $plugin;

    protected function setUp(): void
    {
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->getChildProductFromStoreIdMock = $this->createMock(GetStoreSpecificProductChildIds::class);
        $this->productRepositoryMock = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getById'])
            ->getMockForAbstractClass();

        $this->plugin = new GetProductChildIds(
            $this->storeManagerMock,
            $this->getChildProductFromStoreIdMock,
            $this->productRepositoryMock
        );
    }

    /**
     *  Test case for beforePrepareProductIndex method with child product visibility and website check.
     *
     * @return void
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function testBeforePrepareProductIndexWithChildProductVisibilityAndWebsiteCheck(): void
    {
        $dataProviderMock = $this->createMock(DataProvider::class);
        $indexData = [
            1 => ['data'],
            2 => ['data']
        ];
        $productData = [
            'entity_id' => '1',
            'type_id' => Configurable::TYPE_CODE,
        ];
        $storeId = 1;
        $websiteId = 2;

        $storeMock = $this->createMock(StoreInterface::class);
        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->with($storeId)
            ->willReturn($storeMock);

        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->any())
            ->method('isVisibleInSiteVisibility')
            ->willReturn(true);
        $productMock->expects($this->once())
            ->method('getData')
            ->willReturn(['entity_id' => 1]);
        $productMock->expects($this->any())
            ->method('getWebsiteIds')
            ->willReturn([2]);

        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productData['entity_id'])
            ->willReturn($productMock);

        $this->getChildProductFromStoreIdMock->expects($this->once())
            ->method('process')
            ->with(['entity_id' => 1], $websiteId)
            ->willReturn([2, 3]);

        $childProductMock1 = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isVisibleInSiteVisibility', 'getWebsiteIds'])
            ->getMock();
        $childProductMock1->expects($this->any())
            ->method('isVisibleInSiteVisibility')
            ->willReturn(true);
        $childProductMock1->expects($this->any())
            ->method('getWebsiteIds')
            ->willReturn([2]);

        $childProductMock2 = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isVisibleInSiteVisibility', 'getWebsiteIds'])
            ->getMock();
        $childProductMock2->expects($this->any())
            ->method('isVisibleInSiteVisibility')
            ->willReturn(false);

        $this->productRepositoryMock->expects($this->any())
            ->method('getById')
            ->will($this->returnCallback(
                function ($id) use ($childProductMock1, $childProductMock2) {
                    return $id === 2 ? $childProductMock1 : $childProductMock2;
                }
            ));

        $result = $this->plugin->beforePrepareProductIndex(
            $dataProviderMock,
            $indexData,
            $productData,
            $storeId
        );

        $expectedIndexData = [
            1 => ['data'],
            2 => ['data'],
        ];

        $this->assertEquals([$expectedIndexData, $productData, $storeId], $result);
    }
}
