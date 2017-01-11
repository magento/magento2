<?php
/**
 * Test class for \Magento\Sales\Block\Adminhtml\Order\Create\Form
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create;

/**
 * @magentoAppArea adminhtml
 */
class FormTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Sales\Block\Adminhtml\Order\Create\Form */
    protected $_orderCreateBlock;

    /** @var \Magento\Framework\ObjectManagerInterface */
    protected $_objectManager;

    /**
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $sessionMock = $this->getMockBuilder(
            \Magento\Backend\Model\Session\Quote::class
        )->disableOriginalConstructor()->setMethods(
            ['getCustomerId', 'getQuote', 'getStoreId', 'getStore']
        )->getMock();
        $sessionMock->expects($this->any())->method('getCustomerId')->will($this->returnValue(1));

        $quote = $this->_objectManager->create(\Magento\Quote\Model\Quote::class)->load(1);
        $sessionMock->expects($this->any())->method('getQuote')->will($this->returnValue($quote));

        $sessionMock->expects($this->any())->method('getStoreId')->will($this->returnValue(1));

        $storeMock = $this->getMockBuilder(
            \Magento\Store\Model\Store::class
        )->disableOriginalConstructor()->setMethods(
            ['getCurrentCurrencyCode']
        )->getMock();
        $storeMock->expects($this->any())->method('getCurrentCurrencyCode')->will($this->returnValue('USD'));
        $sessionMock->expects($this->any())->method('getStore')->will($this->returnValue($storeMock));

        /** @var \Magento\Framework\View\LayoutInterface $layout */
        $layout = $this->_objectManager->get(\Magento\Framework\View\LayoutInterface::class);
        $this->_orderCreateBlock = $layout->createBlock(
            \Magento\Sales\Block\Adminhtml\Order\Create\Form::class,
            'order_create_block' . rand(),
            ['sessionQuote' => $sessionMock]
        );
        parent::setUp();
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testOrderDataJson()
    {
        /** @var array $addressIds */
        $addressIds = $this->setUpMockAddress();
        $orderDataJson = $this->_orderCreateBlock->getOrderDataJson();
        $expectedOrderDataJson = <<<ORDER_DATA_JSON
        {
            "customer_id":1,
            "addresses":
                {"{$addressIds[0]}":
                    {"firstname":"John","lastname":"Smith","company":false,"street":"Green str, 67","city":"CityM",
                        "country_id":"US",
                        "region":"Alabama","region_id":1,
                        "postcode":"75477","telephone":"3468676","vat_id":false},
                 "{$addressIds[1]}":
                    {"firstname":"John","lastname":"Smith","company":false,"street":"Black str, 48","city":"CityX",
                        "country_id":"US",
                        "region":"Alabama","region_id":1,
                        "postcode":"47676","telephone":"3234676","vat_id":false}
                 },
             "store_id":1,"currency_symbol":"$","shipping_method_reseted":true,"payment_method":null
         }
ORDER_DATA_JSON;

        $this->assertEquals(json_decode($expectedOrderDataJson), json_decode($orderDataJson));
    }

    private function setUpMockAddress()
    {
        /** @var \Magento\Customer\Api\Data\RegionInterfaceFactory $regionFactory */
        $regionFactory = $this->_objectManager->create(\Magento\Customer\Api\Data\RegionInterfaceFactory::class);

        /** @var \Magento\Customer\Api\Data\AddressInterfaceFactory $addressFactory */
        $addressFactory = $this->_objectManager->create(\Magento\Customer\Api\Data\AddressInterfaceFactory::class);
        /** @var \Magento\Customer\Api\AddressRepositoryInterface $addressRepository */
        $addressRepository = $this->_objectManager->create(\Magento\Customer\Api\AddressRepositoryInterface::class);

        $addressData1 = $addressFactory->create()->setCountryId(
            'US'
        )->setCustomerId(
            1
        )->setIsDefaultBilling(
            true
        )->setIsDefaultShipping(
            true
        )->setPostcode(
            '75477'
        )->setRegion(
            $regionFactory->create()->setRegionCode('AL')->setRegion('Alabama')->setRegionId(1)
        )->setStreet(
            ['Green str, 67']
        )->setTelephone(
            '3468676'
        )->setCity(
            'CityM'
        )->setFirstname(
            'John'
        )->setLastname(
            'Smith'
        );

        $addressData2 = $addressFactory->create()->setCountryId(
            'US'
        )->setCustomerId(
            1
        )->setIsDefaultBilling(
            false
        )->setIsDefaultShipping(
            false
        )->setPostcode(
            '47676'
        )->setRegion(
            $regionFactory->create()->setRegionCode('AL')->setRegion('Alabama')->setRegionId(1)
        )->setStreet(
            ['Black str, 48']
        )->setCity(
            'CityX'
        )->setTelephone(
            '3234676'
        )->setFirstname(
            'John'
        )->setLastname(
            'Smith'
        );

        $savedAddress1 = $addressRepository->save($addressData1);
        $savedAddress2 = $addressRepository->save($addressData2);

        return [$savedAddress1->getId(), $savedAddress2->getId()];
    }
}
