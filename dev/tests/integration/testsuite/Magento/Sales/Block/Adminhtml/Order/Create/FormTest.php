<?php
/**
 * Test class for \Magento\Sales\Block\Adminhtml\Order\Create\Form
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create;

use Magento\Customer\Service\V1;

/**
 * @magentoAppArea adminhtml
 */
class FormTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Sales\Block\Adminhtml\Order\Create\Form */
    protected $_orderCreateBlock;

    /** @var \Magento\Framework\ObjectManager */
    protected $_objectManager;

    /**
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $sessionMock = $this->getMockBuilder(
            'Magento\Backend\Model\Session\Quote'
        )->disableOriginalConstructor()->setMethods(
            array('getCustomerId', 'getQuote', 'getStoreId', 'getStore')
        )->getMock();
        $sessionMock->expects($this->any())->method('getCustomerId')->will($this->returnValue(1));

        $quote = $this->_objectManager->create('Magento\Sales\Model\Quote')->load(1);
        $sessionMock->expects($this->any())->method('getQuote')->will($this->returnValue($quote));

        $sessionMock->expects($this->any())->method('getStoreId')->will($this->returnValue(1));

        $storeMock = $this->getMockBuilder(
            '\Magento\Store\Model\Store'
        )->disableOriginalConstructor()->setMethods(
            array('getCurrentCurrencyCode')
        )->getMock();
        $storeMock->expects($this->any())->method('getCurrentCurrencyCode')->will($this->returnValue('USD'));
        $sessionMock->expects($this->any())->method('getStore')->will($this->returnValue($storeMock));

        /** @var \Magento\Framework\View\LayoutInterface $layout */
        $layout = $this->_objectManager->get('Magento\Framework\View\LayoutInterface');
        $this->_orderCreateBlock = $layout->createBlock(
            'Magento\Sales\Block\Adminhtml\Order\Create\Form',
            'order_create_block' . rand(),
            array('sessionQuote' => $sessionMock)
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
                        "postcode":"75477","telephone":"3468676","fax":false,"vat_id":false},
                 "{$addressIds[1]}":
                    {"firstname":"John","lastname":"Smith","company":false,"street":"Black str, 48","city":"CityX",
                        "country_id":"US",
                        "region":"Alabama","region_id":1,
                        "postcode":"47676","telephone":"3234676","fax":false,"vat_id":false}
                 },
             "store_id":1,"currency_symbol":"$","shipping_method_reseted":true,"payment_method":null
         }
ORDER_DATA_JSON;

        $this->assertEquals(json_decode($expectedOrderDataJson), json_decode($orderDataJson));
    }

    private function setUpMockAddress()
    {
        $regionBuilder1 = $this->_objectManager->create('\Magento\Customer\Service\V1\Data\RegionBuilder');
        $regionBuilder2 = $this->_objectManager->create('\Magento\Customer\Service\V1\Data\RegionBuilder');

        /** @var \Magento\Customer\Service\V1\Data\AddressBuilder $addressBuilder */
        $addressBuilder = $this->_objectManager->create('Magento\Customer\Service\V1\Data\AddressBuilder');
        /** @var \Magento\Customer\Service\V1\CustomerAddressServiceInterface $addressService */
        $addressService = $this->_objectManager->create('Magento\Customer\Service\V1\CustomerAddressServiceInterface');

        $addressData1 = $addressBuilder->setId(
            1
        )->setCountryId(
            'US'
        )->setCustomerId(
            1
        )->setDefaultBilling(
            true
        )->setDefaultShipping(
            true
        )->setPostcode(
            '75477'
        )->setRegion(
            new V1\Data\Region(
                $regionBuilder1->populateWithArray(
                    array('region_code' => 'AL', 'region' => 'Alabama', 'region_id' => 1)
                )
            )
        )->setStreet(
            array('Green str, 67')
        )->setTelephone(
            '3468676'
        )->setCity(
            'CityM'
        )->setFirstname(
            'John'
        )->setLastname(
            'Smith'
        )->create();

        $addressData2 = $addressBuilder->setId(
            2
        )->setCountryId(
            'US'
        )->setCustomerId(
            1
        )->setDefaultBilling(
            false
        )->setDefaultShipping(
            false
        )->setPostcode(
            '47676'
        )->setRegion(
            new V1\Data\Region(
                $regionBuilder2->populateWithArray(
                    array('region_code' => 'AL', 'region' => 'Alabama', 'region_id' => 1)
                )
            )
        )->setStreet(
            array('Black str, 48')
        )->setCity(
            'CityX'
        )->setTelephone(
            '3234676'
        )->setFirstname(
            'John'
        )->setLastname(
            'Smith'
        )->create();

        return $addressService->saveAddresses(1, array($addressData1, $addressData2));
    }
}
