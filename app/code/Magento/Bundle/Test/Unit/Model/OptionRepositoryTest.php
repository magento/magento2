<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Bundle\Test\Unit\Model;

use Magento\Bundle\Model\OptionRepository;

class OptionRepositoryTest extends \PHPUnit_Framework_TestCase
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
        $this->productRepositoryMock = $this->getMock('\Magento\Catalog\Api\ProductRepositoryInterface');
        $this->typeMock = $this->getMock('\Magento\Bundle\Model\Product\Type', [], [], '', false);
        $this->optionFactoryMock = $this->getMockBuilder('\Magento\Bundle\Api\Data\OptionInterfaceFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectHelperMock = $this->getMockBuilder('\Magento\Framework\Api\DataObjectHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->optionResourceMock = $this->getMock(
            '\Magento\Bundle\Model\ResourceModel\Option',
            ['delete', '__wakeup', 'save', 'removeOptionSelections'],
            [],
            '',
            false
        );
        $this->storeManagerMock = $this->getMock('\Magento\Store\Model\StoreManagerInterface');
        $this->linkManagementMock = $this->getMock('\Magento\Bundle\Api\ProductLinkManagementInterface');
        $this->optionListMock = $this->getMock('\Magento\Bundle\Model\Product\OptionList', [], [], '', false);
        $this->linkListMock = $this->getMock('\Magento\Bundle\Model\Product\LinksList', [], [], '', false);
        $this->metadataPoolMock = $this->getMock('Magento\Framework\EntityManager\MetadataPool', [], [], '', false);

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
        $productMock = $this->getMock('\Magento\Catalog\Api\Data\ProductInterface');
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
        $productMock = $this->getMock('\Magento\Catalog\Api\Data\ProductInterface');
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE);
        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($productMock);

        $optCollectionMock = $this->getMock('\Magento\Bundle\Model\ResourceModel\Option\Collection', [], [], '', false);
        $this->typeMock->expects($this->once())
            ->method('getOptionsCollection')
            ->with($productMock)
            ->willReturn($optCollectionMock);
        $optionMock = $this->getMock('\Magento\Bundle\Model\Option', [], [], '', false);
        $optCollectionMock->expects($this->once())->method('getItemById')->with($optionId)->willReturn($optionMock);
        $optionMock->expects($this->once())->method('getId')->willReturn(null);

        $this->model->get($productSku, $optionId);
    }

    public function testGet()
    {
        $productSku = 'sku';
        $optionId = 100;
        $optionData = ['title' => 'option title'];

        $productMock = $this->getMock(
            '\Magento\Catalog\Model\Product',
            ['getTypeId', 'getTypeInstance', 'getStoreId', 'getPriceType', '__wakeup', 'getSku'],
            [],
            '',
            false
        );
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE);
        $productMock->expects($this->once())->method('getSku')->willReturn($productSku);

        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($productMock);

        $optCollectionMock = $this->getMock('\Magento\Bundle\Model\ResourceModel\Option\Collection', [], [], '', false);
        $this->typeMock->expects($this->once())
            ->method('getOptionsCollection')
            ->with($productMock)
            ->willReturn($optCollectionMock);
        $optionMock = $this->getMock('\Magento\Bundle\Model\Option', [], [], '', false);
        $optCollectionMock->expects($this->once())->method('getItemById')->with($optionId)->willReturn($optionMock);

        $optionMock->expects($this->exactly(2))->method('getId')->willReturn(1);
        $optionMock->expects($this->exactly(2))->method('getTitle')->willReturn($optionData['title']);
        $optionMock->expects($this->once())->method('getData')->willReturn($optionData);

        $linkMock = ['item'];
        $this->linkListMock->expects($this->once())->method('getItems')->with($productMock, 100)->willReturn($linkMock);

        $newOptionMock = $this->getMock('\Magento\Bundle\Api\Data\OptionInterface');
        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with($newOptionMock, $optionData, '\Magento\Bundle\Api\Data\OptionInterface')
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
        $optionMock = $this->getMock('\Magento\Bundle\Model\Option', [], [], '', false);
        $this->optionResourceMock->expects($this->once())->method('delete')->with($optionMock)->willReturnSelf();
        $this->assertTrue($this->model->delete($optionMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Cannot delete option with id 1
     */
    public function testDeleteThrowsExceptionIfCannotDelete()
    {
        $optionMock = $this->getMock('\Magento\Bundle\Model\Option', [], [], '', false);
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
        $productMock = $this->getMock('\Magento\Catalog\Api\Data\ProductInterface');
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE);
        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($productMock);

        $optionMock = $this->getMock('\Magento\Bundle\Model\Option', [], [], '', false);

        $optCollectionMock = $this->getMock('\Magento\Bundle\Model\ResourceModel\Option\Collection', [], [], '', false);
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

        $productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $productMock->expects($this->once())->method('getData')->willReturn($productId);
        $productMock->expects($this->once())->method('getStoreId')->willReturn($storeId);

        $optionMock = $this->getMock(
            '\Magento\Bundle\Model\Option',
            ['setStoreId', 'setParentId', 'getProductLinks', 'getOptionId', 'getResource'],
            [],
            '',
            false
        );
        $metadataMock = $this->getMock(
            'Magento\Framework\EntityManager\EntityMetadata',
            [],
            [],
            '',
            false
        );
        $metadataMock->expects($this->once())->method('getLinkField')->willReturn('product_option');
        $optionMock->expects($this->once())->method('setParentId')->with($productId)->willReturnSelf();

        $this->metadataPoolMock->expects($this->once())->method('getMetadata')
            ->with(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->willReturn($metadataMock);
        $optionMock->expects($this->atLeastOnce())->method('getOptionId')->willReturn($optionId);

        $productLinkUpdate = $this->getMock('\Magento\Bundle\Api\Data\LinkInterface');
        $productLinkUpdate->expects($this->any())->method('getId')->willReturn($existingLinkToUpdateId);
        $productLinkNew = $this->getMock('\Magento\Bundle\Api\Data\LinkInterface');
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

        $productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $productMock->expects($this->once())->method('getData')->willReturn($productId);
        $productMock->expects($this->once())->method('getStoreId')->willReturn($storeId);
        $productMock->expects($this->any())->method('getSku')->willReturn($productSku);

        $optionMock = $this->getMock(
            '\Magento\Bundle\Model\Option',
            [
                'setStoreId',
                'setParentId',
                'getProductLinks',
                'getOptionId',
                'setOptionId',
                'setDefaultTitle',
                'getTitle',
                'getResource'
            ],
            [],
            '',
            false
        );
        $metadataMock = $this->getMock(
            'Magento\Framework\EntityManager\EntityMetadata',
            [],
            [],
            '',
            false
        );
        $metadataMock->expects($this->once())->method('getLinkField')->willReturn('product_option');
        $optionMock->expects($this->once())->method('setParentId')->with($productId)->willReturnSelf();

        $this->metadataPoolMock->expects($this->once())->method('getMetadata')
            ->with(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->willReturn($metadataMock);
        $optionMock->method('getOptionId')
            ->willReturn($optionId);

        $productLink1 = $this->getMock('\Magento\Bundle\Api\Data\LinkInterface');
        $productLink2 = $this->getMock('\Magento\Bundle\Api\Data\LinkInterface');
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

        $productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $productMock->expects($this->once())->method('getData')->willReturn($productId);
        $productMock->expects($this->once())->method('getStoreId')->willReturn($storeId);
        $productMock->expects($this->any())->method('getSku')->willReturn($productSku);

        $optionMock = $this->getMock(
            '\Magento\Bundle\Model\Option',
            [
                'setStoreId',
                'setParentId',
                'getProductLinks',
                'getOptionId',
                'setOptionId',
                'setDefaultTitle',
                'getTitle',
                'getResource'
            ],
            [],
            '',
            false
        );
        $metadataMock = $this->getMock(
            'Magento\Framework\EntityManager\EntityMetadata',
            [],
            [],
            '',
            false
        );
        $metadataMock->expects($this->once())->method('getLinkField')->willReturn('product_option');
        $optionMock->expects($this->once())->method('setParentId')->with($productId)->willReturnSelf();

        $this->metadataPoolMock->expects($this->once())->method('getMetadata')
            ->with(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->willReturn($metadataMock);
        $optionMock->method('getOptionId')
            ->will($this->onConsecutiveCalls(null, null, $optionId, $optionId, $optionId));

        $productLink1 = $this->getMock('\Magento\Bundle\Api\Data\LinkInterface');
        $productLink2 = $this->getMock('\Magento\Bundle\Api\Data\LinkInterface');
        $optionMock->expects($this->exactly(2))
            ->method('getProductLinks')
            ->willReturn([$productLink1, $productLink2]);

        $this->optionResourceMock->expects($this->once())->method('save')->with($optionMock)
            ->willThrowException($this->getMock('\Exception'));
        $this->model->save($productMock, $optionMock);
    }

    public function testGetList()
    {
        $productSku = 'simple';
        $productMock = $this->getMock('\Magento\Catalog\Api\Data\ProductInterface');
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
        $productMock = $this->getMock('\Magento\Catalog\Api\Data\ProductInterface');
        $productMock->expects($this->once())->method('getTypeId')->willReturn('simple');
        $this->productRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($productMock);
        $this->assertEquals(['object'], $this->model->getList($productSku));
    }
}
