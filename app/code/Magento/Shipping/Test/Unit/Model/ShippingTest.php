<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Test\Unit\Model;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\CatalogInventory\Model\Stock\Item as StockItem;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Shipping\Model\Carrier\AbstractCarrierInterface;
use Magento\Shipping\Model\CarrierFactory;
use Magento\Shipping\Model\Shipping;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\Store;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Unit tests for \Magento\Shipping\Model\Shipping class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShippingTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test identification number of product.
     *
     * @var int
     */
    protected $productId = 1;

    /**
     * @var Shipping
     */
    protected $shipping;

    /**
     * @var MockObject|StockRegistry
     */
    protected $stockRegistry;

    /**
     * @var MockObject|StockItem
     */
    protected $stockItemData;

    /**
     * @var MockObject|AbstractCarrierInterface
     */
    private $carrier;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfig;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->stockRegistry = $this->createMock(StockRegistry::class);
        $this->stockItemData = $this->createMock(StockItem::class);
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);

        $this->shipping = (new ObjectManagerHelper($this))->getObject(
            Shipping::class,
            [
                'stockRegistry' => $this->stockRegistry,
                'carrierFactory' => $this->getCarrierFactory(),
                'scopeConfig' => $this->scopeConfig,
            ]
        );
    }

    /**
     * Compose Packages For Carrier.
     *
     * @return void
     */
    public function testComposePackages()
    {
        $request = new RateRequest();
        $item = $this->getMockBuilder(QuoteItem::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getQty',
                    'getIsQtyDecimal',
                    'getProductType',
                    'getProduct',
                    'getWeight',
                    '__wakeup',
                    'getStore',
                ]
            )->getMock();
        $product = $this->createMock(Product::class);

        $item->method('getQty')->will($this->returnValue(1));
        $item->method('getWeight')->will($this->returnValue(10));
        $item->method('getIsQtyDecimal')->will($this->returnValue(true));
        $item->method('getProductType')->will($this->returnValue(ProductType::TYPE_SIMPLE));
        $item->method('getProduct')->will($this->returnValue($product));

        $store = $this->createPartialMock(Store::class, ['getWebsiteId']);
        $store->method('getWebsiteId')->will($this->returnValue(10));
        $item->method('getStore')->will($this->returnValue($store));

        $product->method('getId')->will($this->returnValue($this->productId));
        $request->setData('all_items', [$item]);

        $this->stockItemData->method('getIsDecimalDivided')->will($this->returnValue(true));

        /** Testable service calls to CatalogInventory module */
        $this->stockRegistry->expects($this->atLeastOnce())->method('getStockItem')
            ->with($this->productId, 10)
            ->will($this->returnValue($this->stockItemData));

        $this->stockItemData->expects($this->atLeastOnce())
            ->method('getEnableQtyIncrements')
            ->will($this->returnValue(true));
        $this->stockItemData->expects($this->atLeastOnce())->method('getQtyIncrements')
            ->will($this->returnValue(0.5));
        $this->carrier->method('getConfigData')
            ->willReturnCallback(
                function ($key) {
                    $configData = [
                        'max_package_weight' => 10,
                    ];
                    return isset($configData[$key]) ? $configData[$key] : 0;
                }
            );

        $this->shipping->composePackagesForCarrier($this->carrier, $request);
    }

    /**
     * Active flag should be set before collecting carrier rates.
     *
     * @return void
     */
    public function testCollectCarrierRatesSetActiveFlag()
    {
        $carrierCode = 'carrier';
        $scopeStore = 'store';
        $this->scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->with(
                'carriers/' . $carrierCode . '/active',
                $scopeStore
            )
            ->willReturn(true);
        $this->carrier->expects($this->atLeastOnce())
            ->method('setActiveFlag')
            ->with('active');

        $this->shipping->collectCarrierRates($carrierCode, new RateRequest());
    }

    /**
     * @return CarrierFactory|MockObject
     */
    private function getCarrierFactory()
    {
        $carrierFactory = $this->getMockBuilder(CarrierFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->carrier = $this->getMockBuilder(AbstractCarrierInterface::class)
            ->setMethods(
                [
                    'setActiveFlag',
                    'checkAvailableShipCountries',
                    'processAdditionalValidation',
                    'getConfigData',
                    'collectRates',
                ]
            )
            ->getMockForAbstractClass();
        $carrierFactory->method('create')->willReturn($this->carrier);

        return $carrierFactory;
    }
}
