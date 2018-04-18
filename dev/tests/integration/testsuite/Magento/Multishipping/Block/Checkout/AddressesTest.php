<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Block\Checkout;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea frontend
 */
class AddressesTest extends \PHPUnit_Framework_TestCase
{
    const FIXTURE_CUSTOMER_ID = 1;

    /**
     * @var \Magento\Multishipping\Block\Checkout\Addresses
     */
    protected $_addresses;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
        $customerRepository = Bootstrap::getObjectManager()->create(
            'Magento\Customer\Api\CustomerRepositoryInterface'
        );
        $customerData = $customerRepository->getById(self::FIXTURE_CUSTOMER_ID);

        /** @var \Magento\Customer\Model\Session $customerSession */
        $customerSession = $this->_objectManager->get('Magento\Customer\Model\Session');
        $customerSession->setCustomerData($customerData);

        /** @var \Magento\Quote\Model\ResourceModel\Quote\Collection $quoteCollection */
        $quoteCollection = $this->_objectManager->get('Magento\Quote\Model\ResourceModel\Quote\Collection');
        /** @var $quote \Magento\Quote\Model\Quote */
        $quote = $quoteCollection->getLastItem();

        /** @var $checkoutSession \Magento\Checkout\Model\Session */
        $checkoutSession = $this->_objectManager->get('Magento\Checkout\Model\Session');
        $checkoutSession->setQuoteId($quote->getId());
        $checkoutSession->setLoadInactive(true);

        $this->_addresses = $this->_objectManager->create(
            'Magento\Multishipping\Block\Checkout\Addresses'
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoDataFixture Magento/Checkout/_files/quote_with_product_and_payment.php
     */
    public function testGetAddressOptions()
    {
        $expectedResult = [
            [
                'value' => '1',
                'label' => 'John Smith, Green str, 67, CityM, Alabama 75477, United States',
            ],
        ];

        $addressAsHtml = $this->_addresses->getAddressOptions();
        $this->assertEquals($expectedResult, $addressAsHtml);
    }
}
