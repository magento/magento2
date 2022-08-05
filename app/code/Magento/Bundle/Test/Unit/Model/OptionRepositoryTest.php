<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model;

use Magento\Bundle\Api\Data\OptionInterface;
use Magento\Bundle\Api\Data\OptionInterfaceFactory;
use Magento\Bundle\Api\ProductLinkManagementInterface;
use Magento\Bundle\Model\Option\SaveAction;
use Magento\Bundle\Model\OptionRepository;
use Magento\Bundle\Model\Product\OptionList;
use Magento\Bundle\Model\Product\Type;
use Magento\Bundle\Model\ResourceModel\Option;
use Magento\Bundle\Model\ResourceModel\Option\Collection;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OptionRepositoryTest extends TestCase
{
    /**
     * @var OptionRepository
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $productRepositoryMock;

    /**
     * @var MockObject
     */
    protected $typeMock;

    /**
     * @var MockObject
     */
    protected $optionFactoryMock;

    /**
     * @var MockObject
     */
    protected $optionResourceMock;

    /**
     * @var MockObject
     */
    protected $storeManagerMock;

    /**
     * @var MockObject
     */
    protected $linkManagementMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var MockObject
     */
    protected $optionListMock;

    /**
     * @var MockObject
     */
    protected $dataObjectHelperMock;

    /**
     * @var SaveAction|MockObject
     */
    private $optionSaveActionMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->productRepositoryMock = $this->getMockForAbstractClass(ProductRepositoryInterface::class);
        $this->typeMock = $this->createMock(Type::class);
        $this->optionFactoryMock = $this->getMockBuilder(OptionInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectHelperMock = $this->getMockBuilder(DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->optionResourceMock = $this->getMockBuilder(Option::class)
            ->addMethods(['get'])
            ->onlyMethods(['delete', '__wakeup', 'save', 'removeOptionSelections'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->linkManagementMock = $this->getMockForAbstractClass(ProductLinkManagementInterface::class);
        $this->optionListMock = $this->createMock(OptionList::class);
        $this->optionSaveActionMock = $this->createMock(SaveAction::class);

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

    public function testGetThrowsExceptionIfProductIsSimple()
    {
        $this->expectException(InputException::class);
        $this->expectExceptionMessage('This is implemented for bundle products only.');

        $productSku = 'sku';
        $productMock = $this->getMockForAbstractClass(ProductInterface::class);
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($productMock);
        $this->model->get($productSku, 100);
    }

    public function testGetThrowsExceptionIfOptionDoesNotExist()
    {
        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('The option that was requested doesn\'t exist. Verify the entity and try again.');

        $productSku = 'sku';
        $optionId = 100;
        $productMock = $this->getMockForAbstractClass(ProductInterface::class);
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE);
        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($productMock);

        $optCollectionMock = $this->createMock(Collection::class);
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

        $productMock = $this->getMockBuilder(Product::class)
            ->addMethods(['getPriceType'])
            ->onlyMethods(['getTypeId', 'getTypeInstance', 'getStoreId', '__wakeup', 'getSku'])
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE);
        $productMock->expects($this->exactly(2))->method('getSku')->willReturn($productSku);

        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($productMock);

        $optCollectionMock = $this->createMock(Collection::class);
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

        $newOptionMock = $this->getMockForAbstractClass(OptionInterface::class);
        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with($newOptionMock, $optionData, OptionInterface::class)
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

    public function testDeleteThrowsExceptionIfCannotDelete()
    {
        $this->expectException(StateException::class);
        $this->expectExceptionMessage('The option with "1" ID can\'t be deleted.');

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
            ->willReturn(
                [
                    'title' => 'Option title',
                    'option_id' => $optionId
                ]
            );

        $this->optionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($optionMock);

        $productMock = $this->getMockBuilder(Product::class)
            ->addMethods(['getPriceType'])
            ->onlyMethods(['getTypeId', 'getTypeInstance', 'getStoreId', '__wakeup', 'getSku'])
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE);
        $productMock->expects($this->exactly(2))->method('getSku')->willReturn($productSku);

        $this->productRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($productMock);

        $optCollectionMock = $this->createMock(Collection::class);
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

        $productMock = $this->getMockBuilder(Product::class)
            ->addMethods(['getPriceType'])
            ->onlyMethods(['getTypeId', 'getTypeInstance', 'getStoreId', '__wakeup', 'getSku'])
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE);

        $this->productRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($productMock);

        $optCollectionMock = $this->createMock(Collection::class);
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

        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->once())->method('getSku')->willReturn($productSku);

        $optionMock = $this->getMockBuilder(\Magento\Bundle\Model\Option::class)
            ->addMethods(['setStoreId', 'setParentId'])
            ->onlyMethods(['getProductLinks', 'getOptionId', 'getResource'])
            ->disableOriginalConstructor()
            ->getMock();

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

        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->once())->method('getSku')->willReturn($productSku);

        $optionMock = $this->getMockBuilder(\Magento\Bundle\Model\Option::class)->addMethods(
            ['setStoreId', 'setParentId']
        )
            ->onlyMethods(['getProductLinks', 'getOptionId', 'getResource'])
            ->disableOriginalConstructor()
            ->getMock();

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
        $productMock = $this->getMockForAbstractClass(ProductInterface::class);
        $productMock->expects($this->once())->method('getTypeId')->willReturn('bundle');
        $this->productRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($productMock);
        $this->optionListMock->expects($this->once())->method('getItems')->with($productMock)->willReturn(['object']);
        $this->assertEquals(['object'], $this->model->getList($productSku));
    }

    public function testGetListException()
    {
        $this->expectException(InputException::class);
        $this->expectExceptionMessage('This is implemented for bundle products only.');

        $productSku = 'simple';
        $productMock = $this->getMockForAbstractClass(ProductInterface::class);
        $productMock->expects($this->once())->method('getTypeId')->willReturn('simple');
        $this->productRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($productMock);
        $this->assertEquals(['object'], $this->model->getList($productSku));
    }
}
