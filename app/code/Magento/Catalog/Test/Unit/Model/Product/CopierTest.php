<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product;

use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Attribute\ScopeOverriddenValue;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Copier;
use Magento\Catalog\Model\Product\CopyConstructorInterface;
use Magento\Catalog\Model\Product\Option\Repository;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product as ProductResourceModel;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\Catalog\Model\Product\Copier class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CopierTest extends TestCase
{
    /**
     * @var Copier
     */
    private $_model;

    /**
     * @var Repository|MockObject
     */
    private $optionRepositoryMock;

    /**
     * @var CopyConstructorInterface|MockObject
     */
    private $copyConstructorMock;

    /**
     * @var ProductFactory|MockObject
     */
    private $productFactoryMock;

    /**
     * @var ScopeOverriddenValue|MockObject
     */
    private $scopeOverriddenValueMock;

    /**
     * @var Product|MockObject
     */
    private $productMock;

    /**
     * @var EntityMetadata|MockObject
     */
    private $metadata;

    /**
     * @ingeritdoc
     */
    protected function setUp(): void
    {
        $this->copyConstructorMock = $this->getMockForAbstractClass(CopyConstructorInterface::class);
        $this->productFactoryMock = $this->createPartialMock(ProductFactory::class, ['create']);
        $this->scopeOverriddenValueMock = $this->createMock(ScopeOverriddenValue::class);
        $this->optionRepositoryMock = $this->createMock(Repository::class);
        $this->productMock = $this->createMock(Product::class);

        $this->metadata = $this->getMockBuilder(EntityMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var MetadataPool|MockObject $metadataPool */
        $metadataPool = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadataPool->expects($this->once())
            ->method('getMetadata')
            ->willReturn($this->metadata);
        $this->_model = new Copier(
            $this->copyConstructorMock,
            $this->productFactoryMock,
            $this->scopeOverriddenValueMock,
            $this->optionRepositoryMock,
            $metadataPool
        );
    }

    /**
     * Test duplicate product
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCopy(): void
    {
        $stockItem = $this->getMockForAbstractClass(StockItemInterface::class);
        $extensionAttributes = $this->getMockBuilder(ProductExtensionInterface::class)
            ->setMethods(['getStockItem', 'setData'])
            ->getMockForAbstractClass();
        $extensionAttributes
            ->expects($this->once())
            ->method('getStockItem')
            ->willReturn($stockItem);
        $extensionAttributes
            ->expects($this->once())
            ->method('setData')
            ->with('stock_item', null);

        $productData = [
            'product data' => ['product data'],
            ProductInterface::EXTENSION_ATTRIBUTES_KEY => $extensionAttributes,
        ];
        $this->productMock->expects($this->atLeastOnce())
            ->method('getWebsiteIds');
        $this->productMock->expects($this->atLeastOnce())
            ->method('getCategoryIds');
        $this->productMock->expects($this->exactly(2))
            ->method('getData')
            ->willReturnMap([
                ['', null, $productData],
                ['linkField', null, '1'],
            ]);

        $entityMock = $this->getMockForAbstractClass(
            AbstractEntity::class,
            [],
            '',
            false,
            true,
            true,
            ['checkAttributeUniqueValue']
        );
        $entityMock->expects($this->once())
            ->method('checkAttributeUniqueValue')
            ->willReturn(true);

        $attributeMock = $this->getMockForAbstractClass(
            AbstractAttribute::class,
            [],
            '',
            false,
            true,
            true,
            ['getEntity']
        );
        $attributeMock->expects($this->once())
            ->method('getEntity')
            ->willReturn($entityMock);

        $resourceMock = $this->getMockBuilder(ProductResourceModel::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttributeRawValue', 'duplicate', 'getAttribute'])
            ->getMock();
        $resourceMock->expects($this->once())
            ->method('getAttributeRawValue')
            ->willReturn('urk-key-1');
        $resourceMock->expects($this->exactly(2))
            ->method('getAttribute')
            ->willReturn($attributeMock);

        $this->productMock->expects($this->exactly(2))
            ->method('getResource')
            ->willReturn($resourceMock);

        $duplicateMock = $this->getMockBuilder(Product::class)
            ->addMethods(
                [
                    'setIsDuplicate',
                    'setOriginalLinkId',
                    'setUrlKey',
                    'setMetaTitle',
                    'setMetaKeyword',
                    'setMetaDescription'
                ]
            )
            ->onlyMethods(
                [
                    'setData',
                    'setOptions',
                    'getData',
                    'setStatus',
                    'setCreatedAt',
                    'setUpdatedAt',
                    'setId',
                    'getEntityId',
                    'save',
                    'setStoreId',
                    'getStoreIds'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->productFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($duplicateMock);

        $duplicateMock->expects($this->once())->method('setOptions')->with([]);
        $duplicateMock->expects($this->once())->method('setIsDuplicate')->with(true);
        $duplicateMock->expects($this->once())->method('setOriginalLinkId')->with(1);
        $duplicateMock->expects($this->once())
            ->method('setStatus')
            ->with(Status::STATUS_DISABLED);
        $duplicateMock->expects($this->atLeastOnce())->method('setStoreId');
        $duplicateMock->expects($this->once())
            ->method('setCreatedAt')
            ->with(null);
        $duplicateMock->expects($this->once())
            ->method('setUpdatedAt')
            ->with(null);
        $duplicateMock->expects($this->once())
            ->method('setId')
            ->with(null);
        $duplicateMock->expects($this->once())
            ->method('setMetaTitle')
            ->with(null);
        $duplicateMock->expects($this->once())
            ->method('setMetaKeyword')
            ->with(null);
        $duplicateMock->expects($this->once())
            ->method('setMetaDescription')
            ->with(null);
        $duplicateMock->expects($this->atLeastOnce())
            ->method('getStoreIds')->willReturn([]);
        $duplicateMock->expects($this->atLeastOnce())
            ->method('setData')
            ->willReturn($duplicateMock);
        $this->copyConstructorMock->expects($this->once())
            ->method('build')
            ->with($this->productMock, $duplicateMock);
        $duplicateMock->expects($this->once())
            ->method('setUrlKey')
            ->with('urk-key-2')
            ->willReturn($duplicateMock);
        $duplicateMock->expects($this->once())
            ->method('save');
        $this->metadata->expects($this->once())
            ->method('getLinkField')
            ->willReturn('linkField');
        $duplicateMock->expects($this->never())
            ->method('getData');
        $this->optionRepositoryMock->expects($this->once())
            ->method('duplicate')
            ->with($this->productMock, $duplicateMock);

        $this->assertEquals($duplicateMock, $this->_model->copy($this->productMock));
    }

    /**
     * Test duplicate product with `UrlAlreadyExistsException` while copy stores url
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testUrlAlreadyExistsExceptionWhileCopyStoresUrl(): void
    {
        $stockItem = $this->getMockBuilder(StockItemInterface::class)
            ->getMock();
        $extensionAttributes = $this->getMockBuilder(ProductExtensionInterface::class)
            ->setMethods(['getStockItem', 'setData'])
            ->getMockForAbstractClass();
        $extensionAttributes
            ->expects($this->once())
            ->method('getStockItem')
            ->willReturn($stockItem);
        $extensionAttributes
            ->expects($this->once())
            ->method('setData')
            ->with('stock_item', null);

        $productData = [
            'product data' => ['product data'],
            ProductInterface::EXTENSION_ATTRIBUTES_KEY => $extensionAttributes,
        ];
        $this->productMock->expects($this->atLeastOnce())->method('getWebsiteIds');
        $this->productMock->expects($this->atLeastOnce())->method('getCategoryIds');
        $this->productMock->expects($this->any())->method('getData')->willReturnMap([
            ['', null, $productData],
            ['linkField', null, '1'],
        ]);

        $entityMock = $this->getMockForAbstractClass(
            AbstractEntity::class,
            [],
            '',
            false,
            true,
            true,
            ['checkAttributeUniqueValue']
        );
        $entityMock->expects($this->exactly(11))
            ->method('checkAttributeUniqueValue')
            ->willReturn(true, false);

        $attributeMock = $this->getMockForAbstractClass(
            AbstractAttribute::class,
            [],
            '',
            false,
            true,
            true,
            ['getEntity']
        );
        $attributeMock->expects($this->any())
            ->method('getEntity')
            ->willReturn($entityMock);

        $resourceMock = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttributeRawValue', 'duplicate', 'getAttribute'])
            ->getMock();
        $resourceMock->expects($this->any())
            ->method('getAttributeRawValue')
            ->willReturn('urk-key-1');
        $resourceMock->expects($this->any())
            ->method('getAttribute')
            ->willReturn($attributeMock);

        $this->productMock->expects($this->any())->method('getResource')->willReturn($resourceMock);

        $duplicateMock = $this->getMockBuilder(Product::class)
            ->addMethods(['setIsDuplicate', 'setOriginalLinkId', 'setUrlKey'])
            ->onlyMethods(
                [
                    'setData',
                    'setOptions',
                    'getData',
                    'setStatus',
                    'setCreatedAt',
                    'setUpdatedAt',
                    'setId',
                    'getEntityId',
                    'save',
                    'setStoreId',
                    'getStoreIds'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->productFactoryMock->expects($this->once())->method('create')->willReturn($duplicateMock);

        $duplicateMock->expects($this->once())->method('setOptions')->with([]);
        $duplicateMock->expects($this->once())->method('setIsDuplicate')->with(true);
        $duplicateMock->expects($this->once())->method('setOriginalLinkId')->with(1);
        $duplicateMock->expects(
            $this->once()
        )->method(
            'setStatus'
        )->with(
            Status::STATUS_DISABLED
        );
        $duplicateMock->expects($this->atLeastOnce())->method('setStoreId');
        $duplicateMock->expects($this->once())->method('setCreatedAt')->with(null);
        $duplicateMock->expects($this->once())->method('setUpdatedAt')->with(null);
        $duplicateMock->expects($this->once())->method('setId')->with(null);
        $duplicateMock->expects($this->atLeastOnce())->method('getStoreIds')->willReturn([1]);
        $duplicateMock->expects($this->atLeastOnce())->method('setData')->willReturn($duplicateMock);
        $this->copyConstructorMock->expects($this->once())->method('build')->with($this->productMock, $duplicateMock);
        $duplicateMock->expects(
            $this->exactly(11)
        )->method(
            'setUrlKey'
        )->with(
            $this->stringContains('urk-key-')
        )->willReturn(
            $duplicateMock
        );
        $duplicateMock->expects($this->once())->method('save');

        $this->scopeOverriddenValueMock->expects($this->once())->method('containsValue')->willReturn(true);

        $this->metadata->expects($this->any())->method('getLinkField')->willReturn('linkField');

        $duplicateMock->expects($this->any())->method('getData')->willReturnMap([
            ['linkField', null, '2'],
        ]);

        $this->expectException(UrlAlreadyExistsException::class);
        $this->_model->copy($this->productMock);
    }
}
