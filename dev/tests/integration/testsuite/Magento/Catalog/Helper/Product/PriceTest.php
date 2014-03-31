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

namespace Magento\Catalog\Helper\Product;

use Magento\Customer\Service\V1\CustomerGroupServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test class for Magento\Catalog\Helper\Product\Price
 */
class PriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Helper\Product\Price
     */
    protected $_helper;

    /**
     * @var CustomerGroupServiceInterface CustomerAccountServiceInterface
     */
    protected $_customerAccountService;

    protected function setUp()
    {
        $this->_helper = Bootstrap::getObjectManager()->get('Magento\Catalog\Helper\Product\Price');
        $this->_customerAccountService = Bootstrap::getObjectManager()->get(
            'Magento\Customer\Service\V1\CustomerAccountServiceInterface'
        );

    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testSetCustomer()
    {
        $customerData = $this->_customerAccountService->getCustomer(1);
        $this->assertInstanceOf('Magento\Catalog\Helper\Product\Price', $this->_helper->setCustomer($customerData));
        $customerDataRetrieved = $this->_helper->getCustomer();
        $this->assertEquals($customerData->__toArray(), $customerDataRetrieved->__toArray());
    }

}
