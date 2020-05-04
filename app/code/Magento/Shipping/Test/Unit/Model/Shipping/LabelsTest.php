<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Shipping\Test\Unit\Model\Shipping;

use Magento\Backend\Model\Auth\Session;
use Magento\Directory\Model\Region;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Config;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Shipment;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\CarrierFactory;
use Magento\Shipping\Model\Shipment\Request;
use Magento\Shipping\Model\Shipment\RequestFactory;
use Magento\Shipping\Model\Shipping\Labels;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Magento\User\Model\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class LabelsTest
 *
 * Test class for \Magento\Shipping\Model\Shipping\Labels
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LabelsTest extends TestCase
{
    /**
     * @var Labels
     */
    protected $labels;

    /**
     * @var MockObject
     */
    protected $request;

    /**
     * @var MockObject
     */
    protected $scopeConfig;

    /**
     * @var MockObject
     */
    protected $region;

    /**
     * @var MockObject
     */
    private $carrierFactory;

    /**
     * @var MockObject
     */
    private $user;

    protected function setUp(): void
    {
        $this->request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();
        $requestFactory = $this->getMockBuilder(RequestFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $requestFactory->expects(static::any())->method('create')->willReturn($this->request);
        $this->carrierFactory = $this->getMockBuilder(CarrierFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $storeManager = $this->getStoreManager();
        $this->user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFirstname', 'getLastname', 'getEmail', 'getName'])
            ->getMock();

        $authSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUser'])
            ->getMock();
        $authSession->expects(static::any())->method('getUser')->willReturn($this->user);
        $regionFactory = $this->getRegionFactory();
        $this->scopeConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMock();
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->labels = $objectManagerHelper->getObject(
            Labels::class,
            [
                'shipmentRequestFactory' => $requestFactory,
                'carrierFactory' => $this->carrierFactory,
                'storeManager' => $storeManager,
                'scopeConfig' => $this->scopeConfig,
                'authSession' => $authSession,
                'regionFactory' => $regionFactory
            ]
        );
    }

    /**
     * @dataProvider requestToShipmentDataProvider
     */
    public function testRequestToShipment($regionId)
    {
        $carrier = $this->getMockBuilder(AbstractCarrier::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->carrierFactory->expects(static::any())->method('create')->willReturn($carrier);
        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->user->expects($this->atLeastOnce())->method('getFirstname')->willReturn('John');
        $this->user->expects($this->atLeastOnce())->method('getLastname')->willReturn('Doe');
        $this->user->expects($this->once())->method('getName')->willReturn('John Doe');
        $this->user->expects($this->once())->method('getEmail')->willReturn('admin@admin.test.com');
        $shippingMethod = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCarrierCode'])
            ->getMock();
        $shippingMethod->expects(static::once())
            ->method('getCarrierCode')
            ->willReturn('usps');

        $order->expects(static::exactly(2))
            ->method('getShippingMethod')
            ->with(true)
            ->willReturn($shippingMethod);

        $address = $this->getRecipientAddress();

        $order->expects(static::once())
            ->method('getShippingAddress')
            ->willReturn($address);
        $order->expects(static::once())
            ->method('getWeight')
            ->willReturn(2);

        $storeId = 33;
        $shipment = $this->getMockBuilder(Shipment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $shipment->expects(static::once())->method('getOrder')->willReturn($order);
        $shipment->expects(static::once())->method('getStoreId')->willReturn($storeId);
        $shipment->expects(static::once())->method('getPackages')->willReturn('');

        $this->scopeConfig->expects(static::any())
            ->method('getValue')
            ->willReturnMap([
                [Shipment::XML_PATH_STORE_REGION_ID, ScopeInterface::SCOPE_STORE, $storeId, $regionId],
                [Shipment::XML_PATH_STORE_ADDRESS1, ScopeInterface::SCOPE_STORE, $storeId, 'Beverly Heals'],
                ['general/store_information', ScopeInterface::SCOPE_STORE, $storeId, [
                    'name' => 'General Store', 'phone' => '(244)1500301'
                ]],
                [Shipment::XML_PATH_STORE_CITY, ScopeInterface::SCOPE_STORE, $storeId, 'LA'],
                [Shipment::XML_PATH_STORE_ZIP, ScopeInterface::SCOPE_STORE, $storeId, '90304'],
                [Shipment::XML_PATH_STORE_COUNTRY_ID, ScopeInterface::SCOPE_STORE, $storeId, 'US'],
                [Shipment::XML_PATH_STORE_ADDRESS2, ScopeInterface::SCOPE_STORE, $storeId, '1st Park Avenue'],
            ]);
        $this->labels->requestToShipment($shipment);
    }

    /**
     * @dataProvider requestToShipmentLocalizedExceptionDataProvider
     */
    public function testRequestToShipmentLocalizedException($isShipmentCarrierNotNull)
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $shipment = $this->getMockBuilder(Shipment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $shippingMethod = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCarrierCode'])
            ->getMock();
        $order->expects($this->atLeastOnce())
            ->method('getShippingMethod')
            ->with(true)
            ->willReturn($shippingMethod);
        $this->carrierFactory
            ->expects(static::any())
            ->method('create')
            ->willReturn($isShipmentCarrierNotNull ? $shippingMethod : null);
        $shipment->expects($this->once())->method('getOrder')->willReturn($order);
        $this->labels->requestToShipment($shipment);
    }

    /**
     * @return MockObject
     */
    protected function getStoreManager()
    {
        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $store->expects(static::any())
            ->method('getBaseCurrencyCode')
            ->willReturn('USD');

        $storeManager = $this->getMockBuilder(StoreManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore'])
            ->getMock();
        $storeManager->expects(static::any())->method('getStore')->willReturn($store);
        return $storeManager;
    }

    /**
     * @return MockObject
     */
    protected function getRegionFactory()
    {
        $this->region = $this->getMockBuilder(Region::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getCode'])
            ->getMock();
        $regionFactory = $this->getMockBuilder(RegionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $regionFactory->expects(static::any())->method('create')->willReturn($this->region);
        return $regionFactory;
    }

    /**
     * @return MockObject
     */
    protected function getRecipientAddress()
    {
        $address = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->getMock();
        $address->expects(static::exactly(2))
            ->method('getRegionCode')
            ->willReturn('CO');
        $address->expects(static::exactly(2))
            ->method('getFirstname')
            ->willReturn('Chimi');
        $address->expects(static::exactly(2))
            ->method('getLastname')
            ->willReturn('Chung');
        $address->expects(static::once())
            ->method('getCompany')
            ->willReturn('Software LLC');
        $address->expects(static::once())
            ->method('getTelephone')
            ->willReturn('(231) 324-123-31');
        $address->expects(static::once())
            ->method('getEmail')
            ->willReturn('chimi.chung@test.com');
        $address->expects(static::exactly(4))
            ->method('getStreetLine')
            ->willReturn('66 Pearl St');
        $address->expects(static::once())
            ->method('getCity')
            ->willReturn('Denver');
        $address->expects(static::once())
            ->method('getPostcode')
            ->willReturn('80203');
        $address->expects(static::once())
            ->method('getCountryId')
            ->willReturn(1);
        return $address;
    }

    /**
     * Data provider to testRequestToShipment
     * @return array
     */
    public function requestToShipmentDataProvider()
    {
        return [
            [
                'CA'
            ],
            [
                null
            ]
        ];
    }

    /**
     * Data provider to testRequestToShipmentLocalizedException
     * @return array
     */
    public function requestToShipmentLocalizedExceptionDataProvider()
    {
        return [
            [
                true
            ],
            [
                false
            ]
        ];
    }
}
