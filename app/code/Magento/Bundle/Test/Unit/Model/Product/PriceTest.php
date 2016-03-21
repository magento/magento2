<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Model\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class PriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $ruleFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeDateMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $catalogHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \Magento\Bundle\Model\Product\Price
     */
    protected $model;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Customer\Api\GroupManagementInterface
     */
    protected $groupManagement;

    protected function setUp()
    {
        $this->ruleFactoryMock = $this->getMock(
            'Magento\CatalogRule\Model\ResourceModel\RuleFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->storeManagerMock = $this->getMock('\Magento\Store\Model\StoreManagerInterface');
        $this->localeDateMock = $this->getMock('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $this->customerSessionMock = $this->getMock('\Magento\Customer\Model\Session', [], [], '', false);
        $this->eventManagerMock = $this->getMock('\Magento\Framework\Event\ManagerInterface');
        $this->catalogHelperMock = $this->getMock('\Magento\Catalog\Helper\Data', [], [], '', false);
        $this->storeMock = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);
        $this->priceCurrency = $this->getMockBuilder('Magento\Framework\Pricing\PriceCurrencyInterface')->getMock();
        $this->groupManagement = $this->getMockBuilder('Magento\Customer\Api\GroupManagementInterface')
            ->getMockForAbstractClass();
        $tpFactory = $this->getMock(
            'Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            'Magento\Bundle\Model\Product\Price',
            [
                'ruleFactory' => $this->ruleFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'localeDate' => $this->localeDateMock,
                'customerSession' => $this->customerSessionMock,
                'eventManager' => $this->eventManagerMock,
                'priceCurrency' => $this->priceCurrency,
                'groupManagement' => $this->groupManagement,
                'tierPriceFactory' => $tpFactory,
                'config' => $scopeConfig,
                'catalogData' => $this->catalogHelperMock
            ]
        );
    }

    /**
     * @param float $finalPrice
     * @param float $specialPrice
     * @param int $callsNumber
     * @param bool $dateInInterval
     * @param float $expected
     *
     * @covers \Magento\Bundle\Model\Product\Price::calculateSpecialPrice
     * @covers \Magento\Bundle\Model\Product\Price::__construct
     * @dataProvider calculateSpecialPrice
     */
    public function testCalculateSpecialPrice($finalPrice, $specialPrice, $callsNumber, $dateInInterval, $expected)
    {
        $this->localeDateMock->expects($this->exactly($callsNumber))
            ->method('isScopeDateInInterval')->will($this->returnValue($dateInInterval));

        $this->storeManagerMock->expects($this->any())
            ->method('getStore')->will($this->returnValue($this->storeMock));

        $this->storeMock->expects($this->any())
            ->method('roundPrice')->will($this->returnArgument(0));

        $this->assertEquals(
            $expected,
            $this->model->calculateSpecialPrice($finalPrice, $specialPrice, date('Y-m-d'), date('Y-m-d'))
        );
    }

    /**
     * @return array
     */
    public function calculateSpecialPrice()
    {
        return [
            [10, null, 0, true, 10],
            [10, false, 0, true, 10],
            [10, 50, 1, false, 10],
            [10, 50, 1, true, 5],
            [0, 50, 1, true, 0],
            [10, 100, 1, true, 10],
        ];
    }

    public function testGetTotalBundleItemsPriceWithNoCustomOptions()
    {
        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->once())
            ->method('hasCustomOptions')
            ->willReturn(false);

        $this->assertEquals(0, $this->model->getTotalBundleItemsPrice($productMock));
    }

    /**
     * @param string|null $value
     * @dataProvider dataProviderWithEmptyOptions
     */
    public function testGetTotalBundleItemsPriceWithEmptyOptions($value)
    {
        $dataObjectMock = $this->getMockBuilder('Magento\Framework\DataObject')
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();

        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->once())
            ->method('hasCustomOptions')
            ->willReturn(true);
        $productMock->expects($this->once())
            ->method('getCustomOption')
            ->with('bundle_selection_ids')
            ->willReturn($dataObjectMock);

        $dataObjectMock->expects($this->once())
            ->method('getValue')
            ->willReturn($value);

        $this->assertEquals(0, $this->model->getTotalBundleItemsPrice($productMock));
    }

    /**
     * @return array
     */
    public function dataProviderWithEmptyOptions()
    {
        return [
            ['a:0:{}'],
            [''],
            [null],
        ];
    }

    public function testGetTotalBundleItemsPriceWithNoItems()
    {
        $storeId = 1;

        $dataObjectMock = $this->getMockBuilder('Magento\Framework\DataObject')
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();

        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $productTypeMock = $this->getMockBuilder('Magento\Bundle\Model\Product\Type')
            ->disableOriginalConstructor()
            ->getMock();

        $selectionsMock = $this->getMockBuilder('Magento\Bundle\Model\ResourceModel\Selection\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->once())
            ->method('hasCustomOptions')
            ->willReturn(true);
        $productMock->expects($this->once())
            ->method('getCustomOption')
            ->with('bundle_selection_ids')
            ->willReturn($dataObjectMock);
        $productMock->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($productTypeMock);
        $productMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);

        $dataObjectMock->expects($this->once())
            ->method('getValue')
            ->willReturn('a:1:{i:0;s:1:"1";}');

        $productTypeMock->expects($this->once())
            ->method('getSelectionsByIds')
            ->with([1], $productMock)
            ->willReturn($selectionsMock);

        $selectionsMock->expects($this->once())
            ->method('addTierPriceData')
            ->willReturnSelf();
        $selectionsMock->expects($this->once())
            ->method('getItems')
            ->willReturn([]);

        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with(
                'prepare_catalog_product_collection_prices',
                ['collection' => $selectionsMock, 'store_id' => $storeId]
            )
            ->willReturnSelf();

        $this->assertEquals(0, $this->model->getTotalBundleItemsPrice($productMock));
    }
}
