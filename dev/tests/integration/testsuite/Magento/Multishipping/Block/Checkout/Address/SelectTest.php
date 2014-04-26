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
        /** @var \Magento\Customer\Service\V1\CustomerAddressServiceInterface $addressService */
        $addressService = Bootstrap::getObjectManager()->create(
            'Magento\Customer\Service\V1\CustomerAddressServiceInterface'
        );
        $fixtureAddressId = 1;
        $address = $addressService->getAddress($fixtureAddressId);
        $addressAsHtml = $this->_selectBlock->getAddressAsHtml($address);
        $this->assertEquals(
            "John Smith<br/>CompanyName<br />Green str, 67<br />CityM,  Alabama, 75477"
                . "<br/>United States<br/>T: 3468676",
            str_replace("\n", '', $addressAsHtml),
            "Address was represented as HTML incorrectly"
        );
    }
}
