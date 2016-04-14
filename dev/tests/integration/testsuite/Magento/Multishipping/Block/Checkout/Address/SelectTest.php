<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Multishipping\Block\Checkout\Address;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea frontend
 */
class SelectTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Multishipping\Block\Checkout\Address\Select */
    protected $_selectBlock;

    protected function setUp()
    {
        $this->_selectBlock = Bootstrap::getObjectManager()->create(
            'Magento\Multishipping\Block\Checkout\Address\Select'
        );
        parent::setUp();
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     */
    public function testGetAddressAsHtml()
    {
        /** @var \Magento\Customer\Api\AddressRepositoryInterface $addressRepository */
        $addressRepository = Bootstrap::getObjectManager()->create(
            'Magento\Customer\Api\AddressRepositoryInterface'
        );
        $fixtureAddressId = 1;
        $address = $addressRepository->getById($fixtureAddressId);
        $addressAsHtml = $this->_selectBlock->getAddressAsHtml($address);
        $this->assertEquals(
            "John Smith<br/>CompanyName<br />Green str, 67<br />CityM,  Alabama, 75477"
                . "<br/>United States<br/>T: 3468676",
            str_replace("\n", '', $addressAsHtml),
            "Address was represented as HTML incorrectly"
        );
    }
}
