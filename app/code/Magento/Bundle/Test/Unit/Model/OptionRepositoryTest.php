<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Unit\Model;

use Magento\Bundle\Model\OptionRepository;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OptionRepositoryTest extends \PHPUnit\Framework\TestCase
{
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
    protected $dataObjectHelperMock;

    /**
     * @var \Magento\Bundle\Model\Option\SaveAction|\PHPUnit_Framework_MockObject_MockObject
     */
    private $optionSaveActionMock;

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
        $this->optionResourceMock = $this->createPartialMock(
            \Magento\Bundle\Model\ResourceModel\Option::class,
            ['get', 'delete', '__wakeup', 'save', 'removeOptionSelections']
        );
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->linkManagementMock = $this->createMock(\Magento\Bundle\Api\ProductLinkManagementInterface::class);
        $this->optionListMock = $this->createMock(\Magento\Bundle\Model\Product\OptionList::class);
        $this->optionSaveActionMock = $this->createMock(\Magento\Bundle\Model\Option\SaveAction::class);

        $this->model = new OptionRepository(
            $this->productRepositoryMock,
            $this->typeMock,
            $this->optionFactoryMock,
            $this->optionResourceMock,
            $this->linkManagementMock,
            $this->optionListMock,
            $this->dataObjectHelperMock,
            $this->optionSaveActionMock
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage This is implemented for bundle products only.
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
     * @expectedExceptionMessage The option that was requested doesn't exist. Verify the entity and try again.
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

        $productMock = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            ['getTypeId', 'getTypeInstance', 'getStoreId', 'getPriceType', '__wakeup', 'getSku']
        );
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE);
        $productMock->expects($this->exactly(2))->method('getSku')->willReturn($productSku);

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
        $this->linkManagementMock->expects($this->once())
            ->method('getChildren')
            ->with($productSku, $optionId)
            ->willReturn($linkMock);

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
     * @expectedExceptionMessage The option with "1" ID can't be deleted.
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

    /**
     * Test successful delete action for given $optionId
     */
    public function testDeleteById()
    {
        $productSku = 'sku';
        $optionId = 100;

        $optionMock = $this->createMock(\Magento\Bundle\Model\Option::class);
        $optionMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($optionId);

        $optionMock->expects($this->once())
            ->method('getData')
            ->willReturn([
                'title' => 'Option title',
                'option_id' => $optionId
            ]);

        $this->optionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($optionMock);

        $productMock = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            ['getTypeId', 'getTypeInstance', 'getStoreId', 'getPriceType', '__wakeup', 'getSku']
        );
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE);
        $productMock->expects($this->exactly(2))->method('getSku')->willReturn($productSku);

        $this->productRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($productMock);

        $optCollectionMock = $this->createMock(\Magento\Bundle\Model\ResourceModel\Option\Collection::class);
        $optCollectionMock->expects($this->once())->method('getItemById')->with($optionId)->willReturn($optionMock);
        $this->typeMock->expects($this->once())
            ->method('getOptionsCollection')
            ->with($productMock)
            ->willReturn($optCollectionMock);

        $this->assertTrue($this->model->deleteById($productSku, $optionId));
    }

    /**
     * Tests if NoSuchEntityException thrown when provided $optionId not found
     */
    public function testDeleteByIdException()
    {
        $productSku = 'sku';
        $optionId = null;

        $optionMock = $this->createMock(\Magento\Bundle\Model\Option::class);
        $optionMock->expects($this->exactly(1))
            ->method('getId')
            ->willReturn($optionId);

        $productMock = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            ['getTypeId', 'getTypeInstance', 'getStoreId', 'getPriceType', '__wakeup', 'getSku']
        );
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE);

        $this->productRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($productMock);

        $optCollectionMock = $this->createMock(\Magento\Bundle\Model\ResourceModel\Option\Collection::class);
        $optCollectionMock->expects($this->once())->method('getItemById')->with($optionId)->willReturn($optionMock);
        $this->typeMock->expects($this->once())
            ->method('getOptionsCollection')
            ->with($productMock)
            ->willReturn($optCollectionMock);

        $this->expectException(NoSuchEntityException::class);

        $this->model->deleteById($productSku, $optionId);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testSaveExistingOption()
    {
        $optionId = 5;

        $productSku = 'sku';

        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $productMock->expects($this->once())->method('getSku')->willReturn($productSku);

        $optionMock = $this->createPartialMock(
            \Magento\Bundle\Model\Option::class,
            ['setStoreId', 'setParentId', 'getProductLinks', 'getOptionId', 'getResource']
        );

        $optionMock->expects($this->atLeastOnce())->method('getOptionId')->willReturn($optionId);

        $this->optionSaveActionMock->expects($this->once())->method('save')->with($productMock, $optionMock)
            ->willReturn($optionMock);

        $this->productRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($productMock);

        $this->productRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($productMock);

        $this->assertEquals($optionId, $this->model->save($productMock, $optionMock));
    }

    public function testSaveNewOption()
    {
        $optionId = 5;

        $productSku = 'sku';

        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $productMock->expects($this->once())->method('getSku')->willReturn($productSku);

        $optionMock = $this->createPartialMock(
            \Magento\Bundle\Model\Option::class,
            ['setStoreId', 'setParentId', 'getProductLinks', 'getOptionId', 'getResource']
        );

        $optionMock->expects($this->atLeastOnce())->method('getOptionId')->willReturn($optionId);

        $this->optionSaveActionMock->expects($this->once())->method('save')->with($productMock, $optionMock)
            ->willReturn($optionMock);

        $this->productRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($productMock);

        $this->productRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($productMock);
        $this->assertEquals($optionId, $this->model->save($productMock, $optionMock));
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
     * @expectedExceptionMessage This is implemented for bundle products only.
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
