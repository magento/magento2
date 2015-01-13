<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Model\Attribute;

/**
 * Class TaxTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TaxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tax\Helper\Data | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mockTaxHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mockTaxRateManagement;

    /**
     * @var \Magento\GoogleShopping\Model\Config | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mockConfig;

    /**
     * @var \Magento\GoogleShopping\Model\Resource\Attribute | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mockResource;

    /**
     * @var \Magento\Customer\Api\GroupManagementInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupManagementMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mockQuoteDetailsBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mockTaxCalculationService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mockRegionFactory;

    /**
     * @var  \Magento\GoogleShopping\Model\Attribute\Tax
     */
    protected $model;

    public function setUp()
    {
        $this->mockTaxHelper = $this->getMockBuilder('\Magento\Tax\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockTaxRateManagement = $this->getMockBuilder('\Magento\Tax\Api\TaxRateManagementInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockConfig = $this->getMockBuilder('\Magento\GoogleShopping\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->groupManagementMock = $this->getMockBuilder('Magento\Customer\Api\GroupManagementInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockQuoteDetailsBuilder = $this->getMockBuilder('\Magento\Tax\Api\Data\QuoteDetailsDataBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['populateWithArray', 'create'])
            ->getMock();
        $this->mockTaxCalculationService = $this->getMockBuilder('Magento\Tax\Api\TaxCalculationInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockRegionFactory = $this->getMockBuilder('\Magento\Directory\Model\RegionFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $arguments = [
            'taxData' => $this->mockTaxHelper,
            'taxRateManagement' => $this->mockTaxRateManagement,
            'groupManagement' => $this->groupManagementMock,
            'config' => $this->mockConfig,
            'quoteDetailsBuilder' => $this->mockQuoteDetailsBuilder,
            'taxCalculationService' => $this->mockTaxCalculationService,
            'regionFactory' => $this->mockRegionFactory,
        ];
        $this->model = $objectManager->getObject('Magento\GoogleShopping\Model\Attribute\Tax', $arguments);
    }

    public function testGetDefaultCustomerTaxClass()
    {
        $taxClassId = 'tax_class_id';
        $store = 'store';
        $this->setUpGetDefaultCustomerTaxClass($taxClassId, $store);

        $reflectionObject = new \ReflectionObject($this->model);
        $reflectionMethod = $reflectionObject->getMethod('_getDefaultCustomerTaxClassId');
        $reflectionMethod->setAccessible(true);
        $this->assertSame($taxClassId, $reflectionMethod->invokeArgs($this->model, [$store]));
    }

    public function testGetRegionsByRegionId()
    {
        $regionId = 1;
        $postalCode = '*';
        $regionCode = '90210';
        $this->setUpGetRegionsByRegionId($regionId, $regionCode);

        $reflectionObject = new \ReflectionObject($this->model);
        $reflectionMethod = $reflectionObject->getMethod('_getRegionsByRegionId');
        $reflectionMethod->setAccessible(true);
        $this->assertSame([$regionCode], $reflectionMethod->invokeArgs($this->model, [$regionId, $postalCode]));
    }

    public function testConvertAttribute()
    {
        $productStoreId = 'product_store_id';
        $sku = 'sku';
        $price = 'price';
        $name = 'name';
        $productTaxClassId = 'product_tax_class_id';
        $customerTaxClassId = 'tax_class_id';
        $postCode = '90210';
        $this->setUpGetDefaultCustomerTaxClass($customerTaxClassId, $productStoreId);
        $this->setUpGetRegionsByRegionId($postCode, '*');
        $this->mockTaxHelper->expects($this->any())->method('getConfig')->will($this->returnSelf());
        $this->mockTaxHelper->expects($this->any())->method('priceIncludesTax')->will($this->returnValue(false));
        $mockTaxRate = $this->getMockBuilder('\Magento\Tax\Api\Data\TaxRateInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $rates = [$mockTaxRate];
        $this->mockTaxRateManagement->expects($this->once())->method('getRatesByCustomerAndProductTaxClassId')->with(
            $customerTaxClassId,
            $productTaxClassId
        )->will($this->returnValue($rates));
        $targetCountry = 'target_country';
        $this->mockConfig->expects($this->once())->method('getTargetCountry')->with($productStoreId)->will(
            $this->returnValue($targetCountry)
        );
        $mockTaxRate->expects($this->once())->method('getTaxCountryId')->will($this->returnValue($targetCountry));
        $mockTaxRate->expects($this->once())->method('getTaxPostcode')->will($this->returnValue($postCode));
        $mockTaxRate->expects($this->any())->method('getTaxRegionId')->will($this->returnValue($postCode));

        $this->mockQuoteDetailsBuilder->expects($this->once())->method('populateWithArray')
            ->with(
                [
                    'billing_address'       => [
                        'country_id'  => $targetCountry,
                        'region'      => ['region_id' => $postCode],
                        'postcode'    => $postCode,
                    ],
                    'shipping_address'      => [
                        'country_id'  => $targetCountry,
                        'region'      => ['region_id' => $postCode],
                        'postcode'    => $postCode,
                    ],
                    'customer_tax_class_key' => [
                        'type' => 'id',
                        'value' => $customerTaxClassId,
                    ],
                    'items'                 => [
                        [
                            'code'              => $sku,
                            'type'              => 'product',
                            'tax_class_key'      => [
                                'type' => 'id',
                                'value' => $productTaxClassId,
                            ],
                            'unit_price'        => $price,
                            'quantity'          => 1,
                            'tax_included'      => 1,
                            'short_description' => $name,
                        ],
                    ],
                ]
            )
            ->will($this->returnSelf());
        $quoteDetailsObject = $this->getMockBuilder('Magento\Tax\Api\Data\QuoteDetailsInterface')
            ->disableOriginalConstructor()->getMock();
        $this->mockQuoteDetailsBuilder->expects($this->once())->method('create')->will(
            $this->returnValue($quoteDetailsObject)
        );
        $taxDetailsObject = $this->getMockBuilder('\Magento\Tax\Api\Data\TaxDetailsInterface')
            ->disableOriginalConstructor()->getMock();
        $this->mockTaxCalculationService->expects($this->once())->method('calculateTax')->with(
            $quoteDetailsObject,
            $productStoreId
        )->will($this->returnValue($taxDetailsObject));
        $taxAmount = 777;
        $subTotal = 555;
        $taxDetailsObject->expects($this->once())->method('getTaxAmount')->will($this->returnValue($taxAmount));
        $taxDetailsObject->expects($this->once())->method('getSubtotal')->will($this->returnValue($subTotal));
        $taxRate = ($taxAmount / $subTotal) * 100;
        // mock product
        $mockProduct = $this->getMockProduct($productStoreId, $productTaxClassId, $sku, $price, $name);
        $mockEntry = $this->getMockEntry();
        $mockEntry->expects($this->once())->method('cleanTaxes');
        $mockEntry->expects($this->once())
            ->method('addTax')
            ->with(
                [
                    'tax_rate'    => $taxRate,
                    'tax_country' => $targetCountry,
                    'tax_region'  => $postCode,
                ]
            );
        $this->assertSame($mockEntry, $this->model->convertAttribute($mockProduct, $mockEntry));
    }

    private function setUpGetDefaultCustomerTaxClass($taxClassId, $store)
    {
        $mockGroup = $this->getMockBuilder('Magento\Customer\Api\Data\GroupInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->groupManagementMock->expects($this->once())
            ->method('getDefaultGroup')
            ->with($store)
            ->will($this->returnValue($mockGroup));

        $mockGroup->expects($this->once())
            ->method('getTaxClassId')
            ->will($this->returnValue($taxClassId));
    }

    private function setUpGetRegionsByRegionId($regionId, $code)
    {
        $mockRegion = $this->getMockBuilder('\Magento\Directory\Model\Region')
            ->disableOriginalConstructor()
            ->setMethods(['getCode', 'load', '__wakeup'])
            ->getMock();

        $mockRegion->expects($this->once())
            ->method('load')
            ->with($regionId)
            ->will($this->returnSelf());

        $mockRegion->expects($this->once())
            ->method('getCode')
            ->will($this->returnValue($code));

        $this->mockRegionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($mockRegion));
    }

    /**
     * Get a mock product object.
     *
     * @param $productStoreId
     * @param $productTaxClassId
     * @param $sku
     * @param $price
     * @param $name
     * @return \Magento\Catalog\Model\Product | \PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockProduct($productStoreId, $productTaxClassId, $sku, $price, $name)
    {
        $mockProduct = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->setMethods(
                [
                    'getTaxClassId',
                    '__wakeup',
                    'getStoreId',
                    'getPriceInfo',
                    'getAdjustments',
                    'getSku',
                    'getPrice',
                    'getName',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $mockProduct->expects($this->once())
            ->method('getPriceInfo')
            ->will($this->returnSelf());
        $mockProduct->expects($this->once())
            ->method('getAdjustments')
            ->will($this->returnValue(['tax' => 'something']));
        $mockProduct->expects($this->any())
            ->method('getStoreId')
            ->will($this->returnValue($productStoreId));
        $mockProduct->expects($this->any())
            ->method('getTaxClassId')
            ->will($this->returnValue($productTaxClassId));
        $mockProduct->expects($this->once())
            ->method('getSku')
            ->will($this->returnValue($sku));
        $mockProduct->expects($this->once())
            ->method('getPrice')
            ->will($this->returnValue($price));
        $mockProduct->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($name));
        return $mockProduct;
    }

    /**
     * Get a mock entry object.
     *
     * @return \Magento\Framework\Gdata\Gshopping\Entry | \PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockEntry()
    {
        $mockEntry = $this->getMockBuilder('\Magento\Framework\Gdata\Gshopping\Entry')
            ->disableOriginalConstructor()
            ->getMock();
        return $mockEntry;
    }
}
