<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use \Magento\Catalog\Model\Product\Copier;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CopierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionRepositoryMock;

    /**
     * @var Copier
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $copyConstructorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadata;

    protected function setUp()
    {
        $this->copyConstructorMock = $this->getMock(\Magento\Catalog\Model\Product\CopyConstructorInterface::class);
        $this->productFactoryMock = $this->getMock(
            \Magento\Catalog\Model\ProductFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->optionRepositoryMock = $this->getMock(
            \Magento\Catalog\Model\Product\Option\Repository::class,
            [],
            [],
            '',
            false
        );
        $this->optionRepositoryMock;
        $this->productMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $this->productMock->expects($this->any())->method('getEntityId')->willReturn(1);

        $this->metadata = $this->getMockBuilder(\Magento\Framework\EntityManager\EntityMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadataPool = $this->getMockBuilder(\Magento\Framework\EntityManager\MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadataPool->expects($this->any())->method('getMetadata')->willReturn($this->metadata);
        $this->_model = new Copier(
            $this->copyConstructorMock,
            $this->productFactoryMock
        );

        $this->setProperties($this->_model, [
            'optionRepository' => $this->optionRepositoryMock,
            'metadataPool' => $metadataPool,
        ]);
    }

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
        $this->productMock->expects($this->any())->method('getData')->willReturnMap([
            ['', null, $productData],
            ['linkField', null, '1'],
        ]);

        $resourceMock = $this->getMock(\Magento\Catalog\Model\ResourceModel\Product::class, [], [], '', false);
        $this->productMock->expects($this->once())->method('getResource')->will($this->returnValue($resourceMock));

        $duplicateMock = $this->getMock(
            \Magento\Catalog\Model\Product::class,
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
                'setStoreId',
                'getEntityId',
                'save',
                'setUrlKey',
                'getUrlKey',
            ],
            [],
            '',
            false
        );
        $this->productFactoryMock->expects($this->once())->method('create')->will($this->returnValue($duplicateMock));

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
        $duplicateMock->expects($this->once())->method('setCreatedAt')->with(null);
        $duplicateMock->expects($this->once())->method('setUpdatedAt')->with(null);
        $duplicateMock->expects($this->once())->method('setId')->with(null);
        $duplicateMock->expects(
            $this->once()
        )->method(
            'setStoreId'
        )->with(
            \Magento\Store\Model\Store::DEFAULT_STORE_ID
        );
        $duplicateMock->expects($this->once())->method('setData')->with($productData);
        $this->copyConstructorMock->expects($this->once())->method('build')->with($this->productMock, $duplicateMock);
        $duplicateMock->expects($this->once())->method('getUrlKey')->willReturn('urk-key-1');
        $duplicateMock->expects($this->once())->method('setUrlKey')->with('urk-key-2');
        $duplicateMock->expects($this->once())->method('save');

        $this->metadata->expects($this->any())->method('getLinkField')->willReturn('linkField');

        $duplicateMock->expects($this->any())->method('getData')->willReturnMap([
            ['linkField', null, '2'],
        ]);        $this->optionRepositoryMock->expects($this->once())
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
