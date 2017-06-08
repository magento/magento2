<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Test\Unit\Model\Shipping;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Model\Order\Shipment;
use Magento\Store\Model\ScopeInterface;

/**
 * Class LabelsTest
 *
 * Test class for \Magento\Shipping\Model\Shipping\Labels
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LabelsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Shipping\Model\Shipping\Labels
     */
    protected $labels;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $region;

    protected function setUp()
    {
        $this->request = $this->getMockBuilder(\Magento\Shipping\Model\Shipment\Request::class)
            ->disableOriginalConstructor()
            ->getMock();
        $requestFactory = $this->getMockBuilder(\Magento\Shipping\Model\Shipment\RequestFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $requestFactory->expects(static::any())->method('create')->willReturn($this->request);

        $carrier = $this->getMockBuilder(\Magento\Shipping\Model\Carrier\AbstractCarrier::class)
            ->disableOriginalConstructor()
            ->getMock();

        $carrierFactory = $this->getMockBuilder(\Magento\Shipping\Model\CarrierFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $carrierFactory->expects(static::any())->method('create')->willReturn($carrier);

        $storeManager = $this->getStoreManager();
        $authSession = $this->getAuthSession();
        $regionFactory = $this->getRegionFactory();

        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMock();

        $bjectManagerHelper = new ObjectManagerHelper($this);
        $this->labels = $bjectManagerHelper->getObject(
            \Magento\Shipping\Model\Shipping\Labels::class,
            [
                'shipmentRequestFactory' => $requestFactory,
                'carrierFactory' => $carrierFactory,
                'storeManager' => $storeManager,
                'scopeConfig' => $this->scopeConfig,
                'authSession' => $authSession,
                'regionFactory' => $regionFactory
            ]
        );
    }

    /**
     * @covers \Magento\Shipping\Model\Shipping\Labels
     */
    public function testRequestToShipment()
    {
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $shippingMethod = $this->getMockBuilder(\Magento\Framework\DataObject::class)
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
        $shipment = $this->getMockBuilder(\Magento\Sales\Model\Order\Shipment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $shipment->expects(static::once())->method('getOrder')->willReturn($order);
        $shipment->expects(static::once())->method('getStoreId')->willReturn($storeId);
        $shipment->expects(static::once())->method('getPackages')->willReturn('');

        $this->scopeConfig->expects(static::any())
            ->method('getValue')
            ->willReturnMap([
                [Shipment::XML_PATH_STORE_REGION_ID, ScopeInterface::SCOPE_STORE, $storeId, 'CA'],
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
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getAuthSession()
    {
        $user = $this->getMockBuilder(\Magento\User\Model\User::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFirstname', 'getLastname', 'getEmail', 'getName'])
            ->getMock();
        $user->expects(static::exactly(2))
            ->method('getFirstname')
            ->willReturn('John');
        $user->expects(static::exactly(2))
            ->method('getLastname')
            ->willReturn('Doe');
        $user->expects(static::once())
            ->method('getName')
            ->willReturn('John Doe');
        $user->expects(static::once())
            ->method('getEmail')
            ->willReturn('admin@admin.test.com');

        $authSession = $this->getMockBuilder(\Magento\Backend\Model\Auth\Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUser'])
            ->getMock();
        $authSession->expects(static::any())->method('getUser')->willReturn($user);
        return $authSession;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getStoreManager()
    {
        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $store->expects(static::any())
            ->method('getBaseCurrencyCode')
            ->willReturn('USD');

        $storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore'])
            ->getMock();
        $storeManager->expects(static::any())->method('getStore')->willReturn($store);
        return $storeManager;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRegionFactory()
    {
        $this->region = $this->getMockBuilder(\Magento\Directory\Model\Region::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getCode'])
            ->getMock();
        $regionFactory = $this->getMockBuilder(\Magento\Directory\Model\RegionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $regionFactory->expects(static::any())->method('create')->willReturn($this->region);
        return $regionFactory;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRecipientAddress()
    {
        $address = $this->getMockBuilder(\Magento\Sales\Model\Order\Address::class)
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
}
