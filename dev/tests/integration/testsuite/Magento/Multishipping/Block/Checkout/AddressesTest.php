<?php
/**
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
     * @var \Magento\Framework\ObjectManager
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = Bootstrap::getObjectManager();
        $customerService = $this->_objectManager->create('Magento\Customer\Service\V1\CustomerAccountService');
        $customerData = $customerService->getCustomer(self::FIXTURE_CUSTOMER_ID);

        /** @var \Magento\Customer\Model\Session $customerSession */
        $customerSession = $this->_objectManager->get('Magento\Customer\Model\Session');
        $customerSession->setCustomerData($customerData);

        /** @var \Magento\Sales\Model\Resource\Quote\Collection $quoteCollection */
        $quoteCollection = $this->_objectManager->get('Magento\Sales\Model\Resource\Quote\Collection');
        /** @var $quote \Magento\Sales\Model\Quote */
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
                'label' => 'John Smith, Green str, 67, CityM, Alabama 75477, United States'
            ]
        ];

        $addressAsHtml = $this->_addresses->getAddressOptions();
        $this->assertEquals($expectedResult, $addressAsHtml);
    }
}
