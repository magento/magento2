<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Sales\Model\Order\ItemRepository;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\DataObject\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Metadata|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadata;

    /**
     * @var \Magento\Sales\Api\Data\OrderItemSearchResultInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultFactory;

    /**
     * @var \Magento\Catalog\Model\ProductOptionProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productOptionProcessorMock;

    /**
     * @var \Magento\Catalog\Model\ProductOptionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productOptionFactory;

    /**
     * @var \Magento\Catalog\Api\Data\ProductOptionExtensionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $extensionFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessor;

    /**
     * @var array
     */
    protected $productOptionData = [];

    protected function setUp()
    {
        $this->objectFactory = $this->getMockBuilder(\Magento\Framework\DataObject\Factory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->metadata = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Metadata::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchResultFactory = $this->getMockBuilder(
            \Magento\Sales\Api\Data\OrderItemSearchResultInterfaceFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->productOptionFactory = $this->getMockBuilder(\Magento\Catalog\Model\ProductOptionFactory::class)
            ->setMethods([
                'create',
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionProcessor = $this->createMock(
            \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class
        );

        $this->extensionFactory = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductOptionExtensionFactory::class)
            ->setMethods([
                'create',
            ])
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage An ID is needed. Set the ID and try again.
     */
    public function testGetWithNoId()
    {
        $model = new ItemRepository(
            $this->objectFactory,
            $this->metadata,
            $this->searchResultFactory,
            $this->productOptionFactory,
            $this->extensionFactory,
            [],
            $this->collectionProcessor
        );

        $model->get(null);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage The entity that was requested doesn't exist. Verify the entity and try again.
     */
    public function testGetEmptyEntity()
    {
        $orderItemId = 1;

        $orderItemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderItemMock->expects($this->once())
            ->method('load')
            ->with($orderItemId)
            ->willReturn($orderItemMock);
        $orderItemMock->expects($this->once())
            ->method('getItemId')
            ->willReturn(null);

        $this->metadata->expects($this->once())
            ->method('getNewInstance')
            ->willReturn($orderItemMock);

        $model = new ItemRepository(
            $this->objectFactory,
            $this->metadata,
            $this->searchResultFactory,
            $this->productOptionFactory,
            $this->extensionFactory,
            [],
            $this->collectionProcessor
        );

        $model->get($orderItemId);
    }

    public function testGet()
    {
        $orderItemId = 1;
        $productType = 'configurable';

        $this->productOptionData = ['option1' => 'value1'];

        $this->getProductOptionExtensionMock();
        $productOption = $this->getProductOptionMock();
        $orderItemMock = $this->getOrderMock($productType, $productOption);

        $orderItemMock->expects($this->once())
            ->method('load')
            ->with($orderItemId)
            ->willReturn($orderItemMock);
        $orderItemMock->expects($this->once())
            ->method('getItemId')
            ->willReturn($orderItemId);

        $this->metadata->expects($this->once())
            ->method('getNewInstance')
            ->willReturn($orderItemMock);

        $model = $this->getModel($orderItemMock, $productType);
        $this->assertSame($orderItemMock, $model->get($orderItemId));

        // Assert already registered
        $this->assertSame($orderItemMock, $model->get($orderItemId));
    }

    public function testGetList()
    {
        $productType = 'configurable';
        $this->productOptionData = ['option1' => 'value1'];
        $searchCriteriaMock = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->getProductOptionExtensionMock();
        $productOption = $this->getProductOptionMock();
        $orderItemMock = $this->getOrderMock($productType, $productOption);

        $searchResultMock = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Item\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchResultMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$orderItemMock]);

        $this->searchResultFactory->expects($this->once())
            ->method('create')
            ->willReturn($searchResultMock);

        $model = $this->getModel($orderItemMock, $productType);
        $this->assertSame($searchResultMock, $model->getList($searchCriteriaMock));
    }

    public function testDeleteById()
    {
        $orderItemId = 1;
        $productType = 'configurable';

        $requestMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderItemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderItemMock->expects($this->once())
            ->method('load')
            ->with($orderItemId)
            ->willReturn($orderItemMock);
        $orderItemMock->expects($this->once())
            ->method('getItemId')
            ->willReturn($orderItemId);
        $orderItemMock->expects($this->once())
            ->method('getProductType')
            ->willReturn($productType);
        $orderItemMock->expects($this->once())
            ->method('getBuyRequest')
            ->willReturn($requestMock);

        $orderItemResourceMock = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\Db\AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderItemResourceMock->expects($this->once())
            ->method('delete')
            ->with($orderItemMock)
            ->willReturnSelf();

        $this->metadata->expects($this->once())
            ->method('getNewInstance')
            ->willReturn($orderItemMock);
        $this->metadata->expects($this->exactly(1))
            ->method('getMapper')
            ->willReturn($orderItemResourceMock);

        $model = $this->getModel($orderItemMock, $productType);
        $this->assertTrue($model->deleteById($orderItemId));
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $orderItemMock
     * @param string $productType
     * @param array $data
     * @return ItemRepository
     */
    protected function getModel(
        \PHPUnit_Framework_MockObject_MockObject $orderItemMock,
        $productType,
        array $data = []
    ) {
        $requestMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $requestUpdateMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $requestUpdateMock->expects($this->any())
            ->method('getData')
            ->willReturn($data);

        $this->productOptionProcessorMock = $this->getMockBuilder(
            \Magento\Catalog\Model\ProductOptionProcessorInterface::class
        )
            ->getMockForAbstractClass();
        $this->productOptionProcessorMock->expects($this->any())
            ->method('convertToProductOption')
            ->with($requestMock)
            ->willReturn($this->productOptionData);
        $this->productOptionProcessorMock->expects($this->any())
            ->method('convertToBuyRequest')
            ->with($orderItemMock)
            ->willReturn($requestUpdateMock);

        $model = new ItemRepository(
            $this->objectFactory,
            $this->metadata,
            $this->searchResultFactory,
            $this->productOptionFactory,
            $this->extensionFactory,
            [
                $productType => $this->productOptionProcessorMock,
                'custom_options' => $this->productOptionProcessorMock
            ],
            $this->collectionProcessor
        );
        return $model;
    }

    /**
     * @param string $productType
     * @param \PHPUnit_Framework_MockObject_MockObject $productOption
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getOrderMock($productType, $productOption)
    {
        $requestMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderItemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderItemMock->expects($this->once())
            ->method('getProductType')
            ->willReturn($productType);
        $orderItemMock->expects($this->once())
            ->method('getBuyRequest')
            ->willReturn($requestMock);
        $orderItemMock->expects($this->any())
            ->method('getProductOption')
            ->willReturn(null);
        $orderItemMock->expects($this->any())
            ->method('setProductOption')
            ->with($productOption)
            ->willReturnSelf();

        return $orderItemMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getProductOptionMock()
    {
        $productOption = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductOptionInterface::class)
            ->getMockForAbstractClass();
        $productOption->expects($this->any())
            ->method('getExtensionAttributes')
            ->willReturn(null);

        $this->productOptionFactory->expects($this->any())
            ->method('create')
            ->willReturn($productOption);

        return $productOption;
    }

    protected function getProductOptionExtensionMock()
    {
        $productOptionExtension = $this->getMockBuilder(
            \Magento\Catalog\Api\Data\ProductOptionExtensionInterface::class
        )
            ->setMethods([
                'setData',
            ])
            ->getMockForAbstractClass();
        $productOptionExtension->expects($this->any())
            ->method('setData')
            ->with(key($this->productOptionData), current($this->productOptionData))
            ->willReturnSelf();

        $this->extensionFactory->expects($this->any())
            ->method('create')
            ->willReturn($productOptionExtension);
    }
}
