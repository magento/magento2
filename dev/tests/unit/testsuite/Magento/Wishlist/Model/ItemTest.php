<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Model;

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
     * @var \Magento\Catalog\Model\Resource\Url|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $catalogUrl;

    /**
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productTypeConfig;

    /**
     * @var \Magento\Wishlist\Model\Resource\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \Magento\Wishlist\Model\Resource\Item\Collection|\PHPUnit_Framework_MockObject_MockObject
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
     * @var \Magento\Wishlist\Model\Resource\Item\Option\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
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
        $this->catalogUrl = $this->getMockBuilder('Magento\Catalog\Model\Resource\Url')
            ->disableOriginalConstructor()
            ->getMock();
        $this->optionFactory = $this->getMockBuilder('Magento\Wishlist\Model\Item\OptionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->itemOptFactory = $this->getMockBuilder('Magento\Wishlist\Model\Resource\Item\Option\CollectionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->productTypeConfig = $this->getMockBuilder('Magento\Catalog\Model\ProductTypes\ConfigInterface')
            ->getMock();
        $this->productRepository = $this->getMock('Magento\Catalog\Api\ProductRepositoryInterface');
        $this->resource = $this->getMockBuilder('Magento\Wishlist\Model\Resource\Item')
            ->disableOriginalConstructor()
            ->getMock();
        $this->collection = $this->getMockBuilder('Magento\Wishlist\Model\Resource\Item\Collection')
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
        $actualOptions  = $this->model->getOptions();
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
            ['second_key',$optionMock],
            ['third_key', new \Magento\Framework\Object(['code' => 'third_key', 'product' => $productMock])],
        ];
    }

    public function testCompareOptionsPositive()
    {
        $code = 'someOption';
        $optionValue = 100;
        $optionsOneMock = $this->getMockBuilder('Magento\Sales\Model\Quote\Item')
            ->disableOriginalConstructor()
            ->setMethods(['getCode', '__wakeup', 'getValue'])
            ->getMock();
        $optionsTwoMock = $this->getMockBuilder('Magento\Sales\Model\Quote\Item')
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
        $optionsOneMock = $this->getMockBuilder('Magento\Sales\Model\Quote\Item')
            ->disableOriginalConstructor()
            ->setMethods(['getCode', '__wakeup', 'getValue'])
            ->getMock();
        $optionsTwoMock = $this->getMockBuilder('Magento\Sales\Model\Quote\Item')
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
        $optionsOneMock = $this->getMockBuilder('Magento\Sales\Model\Quote\Item')
            ->disableOriginalConstructor()
            ->setMethods(['getCode', '__wakeup'])
            ->getMock();
        $optionsTwoMock = $this->getMockBuilder('Magento\Sales\Model\Quote\Item')
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
}
