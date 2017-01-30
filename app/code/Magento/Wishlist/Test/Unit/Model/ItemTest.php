<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\Unit\Model;

use \Magento\Wishlist\Model\Item;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Url|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $catalogUrl;

    /**
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productTypeConfig;

    /**
     * @var \Magento\Wishlist\Model\ResourceModel\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \Magento\Wishlist\Model\ResourceModel\Item\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collection;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $date;

    /**
     * @var \Magento\Wishlist\Model\Item\OptionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionFactory;

    /**
     * @var \Magento\Wishlist\Model\ResourceModel\Item\Option\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemOptFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepository;

    /**
     * @var Item
     */
    protected $model;

    public function setUp()
    {
        $context = $this->getMockBuilder('Magento\Framework\Model\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->registry = $this->getMockBuilder('Magento\Framework\Registry')
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->getMock();
        $this->date = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime\DateTime')
            ->disableOriginalConstructor()
            ->getMock();
        $this->catalogUrl = $this->getMockBuilder('Magento\Catalog\Model\ResourceModel\Url')
            ->disableOriginalConstructor()
            ->getMock();
        $this->optionFactory = $this->getMockBuilder('Magento\Wishlist\Model\Item\OptionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->itemOptFactory =
            $this->getMockBuilder('Magento\Wishlist\Model\ResourceModel\Item\Option\CollectionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->productTypeConfig = $this->getMockBuilder('Magento\Catalog\Model\ProductTypes\ConfigInterface')
            ->getMock();
        $this->productRepository = $this->getMock('Magento\Catalog\Api\ProductRepositoryInterface');
        $this->resource = $this->getMockBuilder('Magento\Wishlist\Model\ResourceModel\Item')
            ->disableOriginalConstructor()
            ->getMock();
        $this->collection = $this->getMockBuilder('Magento\Wishlist\Model\ResourceModel\Item\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new Item(
            $context,
            $this->registry,
            $this->storeManager,
            $this->date,
            $this->catalogUrl,
            $this->optionFactory,
            $this->itemOptFactory,
            $this->productTypeConfig,
            $this->productRepository,
            $this->resource,
            $this->collection,
            []
        );
    }

    /**
     * @dataProvider getOptionsDataProvider
     */
    public function testAddGetOptions($code, $option)
    {
        $this->assertEmpty($this->model->getOptions());
        $optionMock = $this->getMockBuilder('Magento\Wishlist\Model\Item\Option')
            ->disableOriginalConstructor()
            ->setMethods(['setData', 'getCode', '__wakeup'])
            ->getMock();
        $optionMock->expects($this->any())
            ->method('setData')
            ->willReturnSelf();
        $optionMock->expects($this->any())
            ->method('getCode')
            ->willReturn($code);

        $this->optionFactory->expects($this->any())
            ->method('create')
            ->willReturn($optionMock);
        $this->model->addOption($option);
        $this->assertEquals(1, count($this->model->getOptions()));
    }

    /**
     * @dataProvider getOptionsDataProvider
     */
    public function testRemoveOptionByCode($code, $option)
    {
        $this->assertEmpty($this->model->getOptions());
        $optionMock = $this->getMockBuilder('Magento\Wishlist\Model\Item\Option')
            ->disableOriginalConstructor()
            ->setMethods(['setData', 'getCode', '__wakeup'])
            ->getMock();
        $optionMock->expects($this->any())
            ->method('setData')
            ->willReturnSelf();
        $optionMock->expects($this->any())
            ->method('getCode')
            ->willReturn($code);

        $this->optionFactory->expects($this->any())
            ->method('create')
            ->willReturn($optionMock);
        $this->model->addOption($option);
        $this->assertEquals(1, count($this->model->getOptions()));
        $this->model->removeOption($code);
        $actualOptions = $this->model->getOptions();
        $actualOption = array_pop($actualOptions);
        $this->assertTrue($actualOption->isDeleted());
    }

    public function getOptionsDataProvider()
    {
        $optionMock = $this->getMockBuilder('Magento\Wishlist\Model\Item\Option')
            ->disableOriginalConstructor()
            ->setMethods(['getCode', '__wakeup'])
            ->getMock();
        $optionMock->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue('second_key'));

        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();
        return [
            ['first_key', ['code' => 'first_key', 'value' => 'first_data']],
            ['second_key', $optionMock],
            ['third_key', new \Magento\Framework\DataObject(['code' => 'third_key', 'product' => $productMock])],
        ];
    }

    public function testCompareOptionsPositive()
    {
        $code = 'someOption';
        $optionValue = 100;
        $optionsOneMock = $this->getMockBuilder('Magento\Quote\Model\Quote\Item')
            ->disableOriginalConstructor()
            ->setMethods(['getCode', '__wakeup', 'getValue'])
            ->getMock();
        $optionsTwoMock = $this->getMockBuilder('Magento\Quote\Model\Quote\Item')
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup', 'getValue'])
            ->getMock();

        $optionsOneMock->expects($this->once())->method('getCode')->will($this->returnValue($code));
        $optionsOneMock->expects($this->once())->method('getValue')->will($this->returnValue($optionValue));
        $optionsTwoMock->expects($this->once())->method('getValue')->will($this->returnValue($optionValue));

        $result = $this->model->compareOptions(
            [$code => $optionsOneMock],
            [$code => $optionsTwoMock]
        );

        $this->assertTrue($result);
    }

    public function testCompareOptionsNegative()
    {
        $code = 'someOption';
        $optionOneValue = 100;
        $optionTwoValue = 200;
        $optionsOneMock = $this->getMockBuilder('Magento\Quote\Model\Quote\Item')
            ->disableOriginalConstructor()
            ->setMethods(['getCode', '__wakeup', 'getValue'])
            ->getMock();
        $optionsTwoMock = $this->getMockBuilder('Magento\Quote\Model\Quote\Item')
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup', 'getValue'])
            ->getMock();

        $optionsOneMock->expects($this->once())->method('getCode')->will($this->returnValue($code));
        $optionsOneMock->expects($this->once())->method('getValue')->will($this->returnValue($optionOneValue));
        $optionsTwoMock->expects($this->once())->method('getValue')->will($this->returnValue($optionTwoValue));

        $result = $this->model->compareOptions(
            [$code => $optionsOneMock],
            [$code => $optionsTwoMock]
        );

        $this->assertFalse($result);
    }

    public function testCompareOptionsNegativeOptionsTwoHaveNotOption()
    {
        $code = 'someOption';
        $optionsOneMock = $this->getMockBuilder('Magento\Quote\Model\Quote\Item')
            ->disableOriginalConstructor()
            ->setMethods(['getCode', '__wakeup'])
            ->getMock();
        $optionsTwoMock = $this->getMockBuilder('Magento\Quote\Model\Quote\Item')
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup'])
            ->getMock();

        $optionsOneMock->expects($this->once())->method('getCode')->will($this->returnValue($code));

        $result = $this->model->compareOptions(
            [$code => $optionsOneMock],
            ['someOneElse' => $optionsTwoMock]
        );

        $this->assertFalse($result);
    }

    public function testSetAndSaveItemOptions()
    {
        $this->assertEmpty($this->model->getOptions());
        $firstOptionMock = $this->getMockBuilder('Magento\Wishlist\Model\Item\Option')
            ->disableOriginalConstructor()
            ->setMethods(['getCode', 'isDeleted', 'delete', '__wakeup'])
            ->getMock();
        $firstOptionMock->expects($this->any())
            ->method('getCode')
            ->willReturn('first_code');
        $firstOptionMock->expects($this->any())
            ->method('isDeleted')
            ->willReturn(true);
        $firstOptionMock->expects($this->once())
            ->method('delete');

        $secondOptionMock = $this->getMockBuilder('Magento\Wishlist\Model\Item\Option')
            ->disableOriginalConstructor()
            ->setMethods(['getCode', 'save', '__wakeup'])
            ->getMock();
        $secondOptionMock->expects($this->any())
            ->method('getCode')
            ->willReturn('second_code');
        $secondOptionMock->expects($this->once())
            ->method('save');

        $this->model->setOptions([$firstOptionMock, $secondOptionMock]);
        $this->assertNull($this->model->isOptionsSaved());
        $this->model->saveItemOptions();
        $this->assertTrue($this->model->isOptionsSaved());
    }

    public function testGetProductWithException()
    {
        $this->setExpectedException('Magento\Framework\Exception\LocalizedException', __('Cannot specify product.'));
        $this->model->getProduct();
    }

    public function testGetProduct()
    {
        $productId = 1;
        $storeId = 0;
        $this->model->setData('product_id', $productId);
        $this->model->setData('store_id', $storeId);
        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->expects($this->any())
            ->method('setFinalPrice')
            ->with(null);
        $productMock->expects($this->any())
            ->method('setCustomOprtions')
            ->with([]);
        $this->productRepository->expects($this->once())
            ->method('getById')
            ->willReturn($productMock);
        $this->assertEquals($productMock, $this->model->getProduct());
    }
}
