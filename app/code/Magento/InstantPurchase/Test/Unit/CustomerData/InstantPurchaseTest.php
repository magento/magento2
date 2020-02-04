<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\InstantPurchase\Test\Unit\CustomerData;

use Magento\InstantPurchase\CustomerData\InstantPurchase as CustomerData;
use Magento\Customer\Model\Session;
use Magento\InstantPurchase\Model\InstantPurchaseInterface as InstantPurchaseModel;
use Magento\InstantPurchase\Model\Ui\CustomerAddressesFormatter;
use Magento\InstantPurchase\Model\Ui\PaymentTokenFormatter;
use Magento\InstantPurchase\Model\Ui\ShippingMethodFormatter;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\InstantPurchase\Model\InstantPurchaseOption;
use Magento\Customer\Model\Customer;

/**
 * Test class for InstantPurchase Customer Data
 *
 * Class \Magento\InstantPurchase\Test\Unit\CustomerData\InstantPurchaseTest
 */
class InstantPurchaseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var objectManagerHelper
     */
    private $objectManager;

    /**
     * @var CustomerData | \PHPUnit_Framework_MockObject_MockObject
     */
    private $customerData;

    /**
     * @var Session | \PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSession;

    /**
     * @var StoreManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var InstantPurchaseModel | \PHPUnit_Framework_MockObject_MockObject
     */
    private $instantPurchase;

    /**
     * @var PaymentTokenFormatter | \PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentTokenFormatter;

    /**
     * @var CustomerAddressesFormatter | \PHPUnit_Framework_MockObject_MockObject
     */
    private $customerAddressesFormatter;

    /**
     * @var ShippingMethodFormatter | \PHPUnit_Framework_MockObject_MockObject
     */
    private $shippingMethodFormatter;

    /**
     * @var Store | \PHPUnit_Framework_MockObject_MockObject
     */
    private $store;

    /**
     * @var Customer | \PHPUnit_Framework_MockObject_MockObject
     */
    private $customer;

    /**
     * @var InstantPurchaseOption | \PHPUnit_Framework_MockObject_MockObject
     */
    private $instantPurchaseOption;

    /**
     * Setup environment for testing
     */
    protected function setUp()
    {
        $this->customerSession = $this->createMock(Session::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->instantPurchase = $this->createMock(InstantPurchaseModel::class);
        $this->paymentTokenFormatter = $this->createMock(PaymentTokenFormatter::class);
        $this->customerAddressesFormatter = $this->createMock(CustomerAddressesFormatter::class);
        $this->shippingMethodFormatter = $this->createMock(ShippingMethodFormatter::class);
        $this->store = $this->createMock(Store::class);
        $this->customer = $this->createMock(Customer::class);
        $this->instantPurchaseOption = $this->createMock(InstantPurchaseOption::class);

        $this->objectManager = new ObjectManagerHelper($this);
        $this->customerData = $this->objectManager->getObject(
            CustomerData::class,
            [
                'customerSession' => $this->customerSession,
                'storeManager' => $this->storeManager,
                'instantPurchase' => $this->instantPurchase,
                'paymentTokenFormatter' => $this->paymentTokenFormatter,
                'customerAddressesFormatter' => $this->customerAddressesFormatter,
                'shippingMethodFormatter' => $this->shippingMethodFormatter
            ]
        );
    }

    /**
     * Test getSectionData()
     *
     * @param $isLogin
     * @param $isAvailable
     * @param $expected
     * @dataProvider getSectionDataProvider
     */
    public function testGetSectionData($isLogin, $isAvailable, $expected)
    {
        $this->customerSession->expects($this->any())->method('isLoggedIn')->willReturn($isLogin);

        $this->storeManager->expects($this->any())->method('getStore')->willReturn($this->store);

        $this->customerSession->expects($this->any())->method('getCustomer')
            ->willReturn($this->customer);

        $this->instantPurchase->expects($this->any())->method('getOption')
            ->with($this->store, $this->customer)
            ->willReturn($this->instantPurchaseOption);

        $this->instantPurchaseOption->expects($this->any())->method('isAvailable')
            ->willReturn($isAvailable);

        $this->assertEquals($expected, $this->customerData->getSectionData());
    }

    /**
     * Data Provider for test getSectionData()
     *
     * @return array
     */
    public function getSectionDataProvider()
    {
        return [
            'No Login and available instant purchase' => [
                false,
                true,
                ['available' => false]
            ],

            'Login and no available instant purchase option' => [
                true,
                false,
                ['available' => false]
            ],

            'Login and available instant purchase option' => [
                true,
                true,
                [
                    'available' => true,
                    'paymentToken' => [
                        'publicHash' => '',
                        'summary' => ''
                    ],
                    'shippingAddress' => [
                        'id' => null,
                        'summary' => ''
                    ],
                    'billingAddress' => [
                        'id' => null,
                        'summary' => ''
                    ],
                    'shippingMethod' => [
                        'carrier' => null,
                        'method' => null,
                        'summary' => ''
                    ]
                ]
            ]
        ];
    }
}
