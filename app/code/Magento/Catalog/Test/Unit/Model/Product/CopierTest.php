<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Attribute\ScopeOverriddenValue;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Copier;

/**
 * Test for Magento\Catalog\Model\Product\Copier class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CopierTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $optionRepositoryMock;

    /**
     * @var Copier
     */
    protected $_model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $copyConstructorMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $productFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $metadata;

    /**
     * @var ScopeOverriddenValue|\PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeOverriddenValue;

    protected function setUp(): void
    {
        $this->copyConstructorMock = $this->createMock(\Magento\Catalog\Model\Product\CopyConstructorInterface::class);
        $this->productFactoryMock = $this->createPartialMock(
            \Magento\Catalog\Model\ProductFactory::class,
            ['create']
        );
        $this->optionRepositoryMock = $this->createMock(
            \Magento\Catalog\Model\Product\Option\Repository::class
        );
        $this->optionRepositoryMock;
        $this->productMock = $this->createMock(Product::class);
        $this->productMock->expects($this->any())->method('getEntityId')->willReturn(1);
        $this->scopeOverriddenValue = $this->createMock(ScopeOverriddenValue::class);

        $this->metadata = $this->getMockBuilder(\Magento\Framework\EntityManager\EntityMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadataPool = $this->getMockBuilder(\Magento\Framework\EntityManager\MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadataPool->expects($this->any())->method('getMetadata')->willReturn($this->metadata);

        $this->_model = new Copier(
            $this->copyConstructorMock,
            $this->productFactoryMock,
            $this->scopeOverriddenValue
        );

        $this->setProperties(
            $this->_model,
            [
                'optionRepository' => $this->optionRepositoryMock,
                'metadataPool' => $metadataPool,
            ]
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCopy()
    {
        $stockItem = $this->getMockBuilder(\Magento\CatalogInventory\Api\Data\StockItemInterface::class)
            ->getMock();
        $extensionAttributes = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductExtension::class)
            ->setMethods(['getStockItem', 'setData'])
            ->getMock();
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
        $this->productMock->expects($this->any())->method('getData')->willReturnMap(
            [
                ['', null, $productData],
                ['linkField', null, '1'],
            ]
        );

        $entityMock = $this->getMockForAbstractClass(
            \Magento\Eav\Model\Entity\AbstractEntity::class,
            [],
            '',
            false,
            true,
            true,
            ['checkAttributeUniqueValue']
        );
        $entityMock->expects($this->any())
            ->method('checkAttributeUniqueValue')
            ->willReturn(true);

        $attributeMock = $this->getMockForAbstractClass(
            \Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class,
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

        $duplicateMock = $this->createPartialMock(
            Product::class,
            [
                '__wakeup',
                'setData',
                'setOptions',
                'getData',
                'setIsDuplicate',
                'setOriginalLinkId',
                'setStatus',
                'setCreatedAt',
                'setUpdatedAt',
                'setId',
                'getEntityId',
                'save',
                'setUrlKey',
                'setStoreId',
                'getStoreIds',
            ]
        );
        $this->productFactoryMock->expects($this->once())->method('create')->willReturn($duplicateMock);

        $duplicateMock->expects($this->once())->method('setOptions')->with([]);
        $duplicateMock->expects($this->once())->method('setIsDuplicate')->with(true);
        $duplicateMock->expects($this->once())->method('setOriginalLinkId')->with(1);
        $duplicateMock->expects(
            $this->once()
        )->method(
            'setStatus'
        )->with(
            \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED
        );
        $duplicateMock->expects($this->atLeastOnce())->method('setStoreId');
        $duplicateMock->expects($this->once())->method('setCreatedAt')->with(null);
        $duplicateMock->expects($this->once())->method('setUpdatedAt')->with(null);
        $duplicateMock->expects($this->once())->method('setId')->with(null);
        $duplicateMock->expects($this->atLeastOnce())->method('getStoreIds')->willReturn([]);
        $duplicateMock->expects($this->atLeastOnce())->method('setData')->willReturn($duplicateMock);
        $this->copyConstructorMock->expects($this->once())->method('build')->with($this->productMock, $duplicateMock);
        $duplicateMock->expects($this->once())->method('setUrlKey')->with('urk-key-2')->willReturn($duplicateMock);
        $duplicateMock->expects($this->once())->method('save');

        $this->metadata->expects($this->any())->method('getLinkField')->willReturn('linkField');

        $duplicateMock->expects($this->any())->method('getData')->willReturnMap(
            [
                ['linkField', null, '2'],
            ]
        );
        $this->optionRepositoryMock->expects($this->once())
            ->method('duplicate')
            ->with($this->productMock, $duplicateMock);
        $resourceMock->expects($this->once())->method('duplicate')->with(1, 2);

        $this->assertEquals($duplicateMock, $this->_model->copy($this->productMock));
    }

    /**
     * @param $object
     * @param array $properties
     */
    private function setProperties($object, $properties = [])
    {
        $reflectionClass = new \ReflectionClass(get_class($object));
        foreach ($properties as $key => $value) {
            if ($reflectionClass->hasProperty($key)) {
                $reflectionProperty = $reflectionClass->getProperty($key);
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($object, $value);
            }
        }
    }
}
