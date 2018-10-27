<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Bundle\Test\Unit\Model;

use Magento\Bundle\Model\OptionRepository;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OptionRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataPoolMock;

    /**
     * @var \Magento\Bundle\Model\OptionRepository
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $typeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionResourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkManagementMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionListMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkListMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectHelperMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->productRepositoryMock = $this->createMock(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->typeMock = $this->createMock(\Magento\Bundle\Model\Product\Type::class);
        $this->optionFactoryMock = $this->getMockBuilder(\Magento\Bundle\Api\Data\OptionInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectHelperMock = $this->getMockBuilder(\Magento\Framework\Api\DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->optionResourceMock = $this->createPartialMock(\Magento\Bundle\Model\ResourceModel\Option::class, ['delete', '__wakeup', 'save', 'removeOptionSelections']);
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->linkManagementMock = $this->createMock(\Magento\Bundle\Api\ProductLinkManagementInterface::class);
        $this->optionListMock = $this->createMock(\Magento\Bundle\Model\Product\OptionList::class);
        $this->linkListMock = $this->createMock(\Magento\Bundle\Model\Product\LinksList::class);
        $this->metadataPoolMock = $this->createMock(\Magento\Framework\EntityManager\MetadataPool::class);

        $this->model = new OptionRepository(
            $this->productRepositoryMock,
            $this->typeMock,
            $this->optionFactoryMock,
            $this->optionResourceMock,
            $this->storeManagerMock,
            $this->linkManagementMock,
            $this->optionListMock,
            $this->linkListMock,
            $this->dataObjectHelperMock
        );
        $refClass = new \ReflectionClass(OptionRepository::class);
        $refProperty = $refClass->getProperty('metadataPool');
        $refProperty->setAccessible(true);
        $refProperty->setValue($this->model, $this->metadataPoolMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Only implemented for bundle product
     */
    public function testGetThrowsExceptionIfProductIsSimple()
    {
        $productSku = 'sku';
        $productMock = $this->createMock(\Magento\Catalog\Api\Data\ProductInterface::class);
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($productMock);
        $this->model->get($productSku, 100);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Requested option doesn't exist
     */
    public function testGetThrowsExceptionIfOptionDoesNotExist()
    {
        $productSku = 'sku';
        $optionId = 100;
        $productMock = $this->createMock(\Magento\Catalog\Api\Data\ProductInterface::class);
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE);
        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($productMock);

        $optCollectionMock = $this->createMock(\Magento\Bundle\Model\ResourceModel\Option\Collection::class);
        $this->typeMock->expects($this->once())
            ->method('getOptionsCollection')
            ->with($productMock)
            ->willReturn($optCollectionMock);
        $optionMock = $this->createMock(\Magento\Bundle\Model\Option::class);
        $optCollectionMock->expects($this->once())->method('getItemById')->with($optionId)->willReturn($optionMock);
        $optionMock->expects($this->once())->method('getId')->willReturn(null);

        $this->model->get($productSku, $optionId);
    }

    public function testGet()
    {
        $productSku = 'sku';
        $optionId = 100;
        $optionData = ['title' => 'option title'];

        $productMock = $this->createPartialMock(\Magento\Catalog\Model\Product::class, ['getTypeId', 'getTypeInstance', 'getStoreId', 'getPriceType', '__wakeup', 'getSku']);
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE);
        $productMock->expects($this->once())->method('getSku')->willReturn($productSku);

        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($productMock);

        $optCollectionMock = $this->createMock(\Magento\Bundle\Model\ResourceModel\Option\Collection::class);
        $this->typeMock->expects($this->once())
            ->method('getOptionsCollection')
            ->with($productMock)
            ->willReturn($optCollectionMock);
        $optionMock = $this->createMock(\Magento\Bundle\Model\Option::class);
        $optCollectionMock->expects($this->once())->method('getItemById')->with($optionId)->willReturn($optionMock);

        $optionMock->expects($this->exactly(2))->method('getId')->willReturn(1);
        $optionMock->expects($this->exactly(2))->method('getTitle')->willReturn($optionData['title']);
        $optionMock->expects($this->once())->method('getData')->willReturn($optionData);

        $linkMock = ['item'];
        $this->linkListMock->expects($this->once())->method('getItems')->with($productMock, 100)->willReturn($linkMock);

        $newOptionMock = $this->createMock(\Magento\Bundle\Api\Data\OptionInterface::class);
        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with($newOptionMock, $optionData, \Magento\Bundle\Api\Data\OptionInterface::class)
            ->willReturnSelf();
        $newOptionMock->expects($this->once())->method('setOptionId')->with(1)->willReturnSelf();
        $newOptionMock->expects($this->once())
            ->method('setTitle')
            ->with($optionData['title'])
            ->willReturnSelf();
        $newOptionMock->expects($this->once())->method('setSku')->with()->willReturnSelf();
        $newOptionMock->expects($this->once())
            ->method('setProductLinks')
            ->with($linkMock)
            ->willReturnSelf();

        $this->optionFactoryMock->expects($this->once())->method('create')->willReturn($newOptionMock);

        $this->assertEquals($newOptionMock, $this->model->get($productSku, $optionId));
    }

    public function testDelete()
    {
        $optionMock = $this->createMock(\Magento\Bundle\Model\Option::class);
        $this->optionResourceMock->expects($this->once())->method('delete')->with($optionMock)->willReturnSelf();
        $this->assertTrue($this->model->delete($optionMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Cannot delete option with id 1
     */
    public function testDeleteThrowsExceptionIfCannotDelete()
    {
        $optionMock = $this->createMock(\Magento\Bundle\Model\Option::class);
        $optionMock->expects($this->once())->method('getOptionId')->willReturn(1);
        $this->optionResourceMock->expects($this->once())
            ->method('delete')
            ->with($optionMock)
            ->willThrowException(new \Exception());
        $this->model->delete($optionMock);
    }

    public function testDeleteById()
    {
        $productSku = 'sku';
        $optionId = 100;
        $productMock = $this->createMock(\Magento\Catalog\Api\Data\ProductInterface::class);
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE);
        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($productMock);

        $optionMock = $this->createMock(\Magento\Bundle\Model\Option::class);

        $optCollectionMock = $this->createMock(\Magento\Bundle\Model\ResourceModel\Option\Collection::class);
        $this->typeMock->expects($this->once())
            ->method('getOptionsCollection')
            ->with($productMock)
            ->willReturn($optCollectionMock);

        $optCollectionMock->expects($this->once())->method('setIdFilter')->with($optionId)->willReturnSelf();
        $optCollectionMock->expects($this->once())->method('getFirstItem')->willReturn($optionMock);

        $this->optionResourceMock->expects($this->once())->method('delete')->with($optionMock)->willReturnSelf();
        $this->assertTrue($this->model->deleteById($productSku, $optionId));
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testSaveExistingOption()
    {
        $productId = 1;

        $storeId = 2;
        $optionId = 5;
        $existingLinkToUpdateId = '23';

        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $productMock->expects($this->once())->method('getData')->willReturn($productId);
        $productMock->expects($this->once())->method('getStoreId')->willReturn($storeId);
        $optionCollectionMock = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Option\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->typeMock->expects($this->once())
            ->method('getOptionsCollection')
            ->with($productMock)
            ->willReturn($optionCollectionMock);
        $optionCollectionMock->expects($this->once())->method('setIdFilter')->with($optionId)->willReturnSelf();

        $optionMock = $this->createPartialMock(
            \Magento\Bundle\Model\Option::class,
            ['setStoreId', 'setParentId', 'getProductLinks', 'getOptionId', 'getResource']
        );
        $optionCollectionMock->expects($this->once())->method('getFirstItem')->willReturn($optionMock);


        $metadataMock = $this->createMock(\Magento\Framework\EntityManager\EntityMetadata::class);
        $metadataMock->expects($this->once())->method('getLinkField')->willReturn('product_option');

        $this->metadataPoolMock->expects($this->once())->method('getMetadata')
            ->with(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->willReturn($metadataMock);
        $optionMock->expects($this->atLeastOnce())->method('getOptionId')->willReturn($optionId);

        $productLinkUpdate = $this->createMock(\Magento\Bundle\Api\Data\LinkInterface::class);
        $productLinkUpdate->expects($this->any())->method('getId')->willReturn($existingLinkToUpdateId);
        $productLinkNew = $this->createMock(\Magento\Bundle\Api\Data\LinkInterface::class);
        $productLinkNew->expects($this->any())->method('getId')->willReturn(null);
        $optionMock->expects($this->exactly(2))
            ->method('getProductLinks')
            ->willReturn([$productLinkUpdate, $productLinkNew]);

        $this->linkManagementMock->expects($this->exactly(2))
            ->method('addChild')
            ->with($productMock);
        $this->assertEquals($optionId, $this->model->save($productMock, $optionMock));
    }

    public function testSaveNewOption()
    {
        $productId = 1;
        $productSku = 'bundle_sku';
        $storeId = 2;
        $optionId = 5;

        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $productMock->expects($this->once())->method('getData')->willReturn($productId);
        $productMock->expects($this->once())->method('getStoreId')->willReturn($storeId);
        $productMock->expects($this->any())->method('getSku')->willReturn($productSku);

        $optionCollectionMock = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Option\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->typeMock->expects($this->once())
            ->method('getOptionsCollection')
            ->with($productMock)
            ->willReturn($optionCollectionMock);
        $optionCollectionMock->expects($this->once())->method('setIdFilter')->with($optionId)->willReturnSelf();

        $optionMock = $this->createPartialMock(
            \Magento\Bundle\Model\Option::class,
            [
                'setStoreId',
                'setParentId',
                'getProductLinks',
                'getOptionId',
                'setOptionId',
                'setDefaultTitle',
                'getTitle',
                'getResource'
            ]
        );
        $optionCollectionMock->expects($this->once())->method('getFirstItem')->willReturn($optionMock);
        $metadataMock = $this->createMock(
            \Magento\Framework\EntityManager\EntityMetadata::class
        );
        $metadataMock->expects($this->once())->method('getLinkField')->willReturn('product_option');

        $this->metadataPoolMock->expects($this->once())->method('getMetadata')
            ->with(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->willReturn($metadataMock);
        $optionMock->method('getOptionId')
            ->willReturn($optionId);

        $productLink1 = $this->createMock(\Magento\Bundle\Api\Data\LinkInterface::class);
        $productLink2 = $this->createMock(\Magento\Bundle\Api\Data\LinkInterface::class);
        $optionMock->expects($this->exactly(2))
            ->method('getProductLinks')
            ->willReturn([$productLink1, $productLink2]);

        $this->optionResourceMock->expects($this->once())->method('save')->with($optionMock)->willReturnSelf();
        $this->linkManagementMock->expects($this->at(0))
            ->method('addChild')
            ->with($productMock, $optionId, $productLink1);
        $this->linkManagementMock->expects($this->at(1))
            ->method('addChild')
            ->with($productMock, $optionId, $productLink2);
        $this->assertEquals($optionId, $this->model->save($productMock, $optionMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Could not save option
     */
    public function testSaveCanNotSave()
    {
        $productId = 1;
        $productSku = 'bundle_sku';
        $storeId = 2;
        $optionId = 5;

        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $productMock->expects($this->once())->method('getData')->willReturn($productId);
        $productMock->expects($this->once())->method('getStoreId')->willReturn($storeId);
        $productMock->expects($this->any())->method('getSku')->willReturn($productSku);

        $optionCollectionMock = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Option\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->typeMock->expects($this->once())
            ->method('getOptionsCollection')
            ->with($productMock)
            ->willReturn($optionCollectionMock);
        $optionCollectionMock->expects($this->once())->method('setIdFilter')->with($optionId)->willReturnSelf();

        $optionMock = $this->createPartialMock(
            \Magento\Bundle\Model\Option::class,
            [
                'setStoreId',
                'setParentId',
                'getProductLinks',
                'getOptionId',
                'setOptionId',
                'setDefaultTitle',
                'getTitle',
                'getResource'
            ]
        );
        $optionCollectionMock->expects($this->once())->method('getFirstItem')->willReturn($optionMock);
        $metadataMock = $this->createMock(
            \Magento\Framework\EntityManager\EntityMetadata::class
        );
        $metadataMock->expects($this->once())->method('getLinkField')->willReturn('product_option');

        $this->metadataPoolMock->expects($this->once())->method('getMetadata')
            ->with(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->willReturn($metadataMock);
        $optionMock->method('getOptionId')->willReturn($optionId);

        $productLink1 = $this->createMock(\Magento\Bundle\Api\Data\LinkInterface::class);
        $productLink2 = $this->createMock(\Magento\Bundle\Api\Data\LinkInterface::class);
        $optionMock->expects($this->exactly(2))
            ->method('getProductLinks')
            ->willReturn([$productLink1, $productLink2]);

        $this->optionResourceMock->expects($this->once())->method('save')->with($optionMock)
            ->willThrowException($this->createMock(\Exception::class));
        $this->model->save($productMock, $optionMock);
    }

    public function testGetList()
    {
        $productSku = 'simple';
        $productMock = $this->createMock(\Magento\Catalog\Api\Data\ProductInterface::class);
        $productMock->expects($this->once())->method('getTypeId')->willReturn('bundle');
        $this->productRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($productMock);
        $this->optionListMock->expects($this->once())->method('getItems')->with($productMock)->willReturn(['object']);
        $this->assertEquals(['object'], $this->model->getList($productSku));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Only implemented for bundle product
     */
    public function testGetListException()
    {
        $productSku = 'simple';
        $productMock = $this->createMock(\Magento\Catalog\Api\Data\ProductInterface::class);
        $productMock->expects($this->once())->method('getTypeId')->willReturn('simple');
        $this->productRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($productMock);
        $this->assertEquals(['object'], $this->model->getList($productSku));
    }
}
