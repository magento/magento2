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
 * @category    Magento
 * @package     Magento_Sales
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Sales\Model;

class QuoteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoDataFixture Magento/Catalog/_files/product_virtual.php
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testCollectTotalsWithVirtual()
    {
        $quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Sales\Model\Quote');
        $quote->load('test01', 'reserved_order_id');

        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Catalog\Model\Product');
        $product->load(21);
        $quote->addProduct($product);
        $quote->collectTotals();

        $this->assertEquals(2, $quote->getItemsQty());
        $this->assertEquals(1, $quote->getVirtualItemsQty());
        $this->assertEquals(20, $quote->getGrandTotal());
        $this->assertEquals(20, $quote->getBaseGrandTotal());
    }

    public function testSetCustomerData()
    {
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Sales\Model\Quote');

        $customerBuilder = new \Magento\Customer\Service\V1\Dto\CustomerBuilder();
        $expected = $this->_getCustomerDataArray();
        $customerBuilder->populateWithArray($expected);

        $customerDataSet = $customerBuilder->create();
        $this->assertEquals($expected, $customerDataSet->__toArray());
        $quote->setCustomerData($customerDataSet);

        $customerDataRetrieved = $quote->getCustomerData();
        $this->assertEquals($expected, $customerDataRetrieved->__toArray());
        $this->assertEquals('qa@example.com', $quote->getCustomerEmail());
    }

    public function testUpdateCustomerData()
    {
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Sales\Model\Quote');

        $customerBuilder = new \Magento\Customer\Service\V1\Dto\CustomerBuilder();
        $expected = $this->_getCustomerDataArray();

        $customerBuilder->populateWithArray($expected);
        $customerDataSet = $customerBuilder->create();
        $this->assertEquals($expected, $customerDataSet->__toArray());
        $quote->setCustomerData($customerDataSet);

        $expected[\Magento\Customer\Service\V1\Dto\Customer::EMAIL] = 'test@example.com';
        $customerBuilder->populateWithArray($expected);
        $customerDataUpdated = $customerBuilder->create();

        $quote->updateCustomerData($customerDataUpdated);
        $customerDataRetrieved = $quote->getCustomerData();
        $this->assertEquals($expected, $customerDataRetrieved->__toArray());
        $this->assertEquals('test@example.com', $quote->getCustomerEmail());
    }

    protected function _getCustomerDataArray()
    {
        return [
            \Magento\Customer\Service\V1\Dto\Customer::ID => 1,
            \Magento\Customer\Service\V1\Dto\Customer::CONFIRMATION => 'test',
            \Magento\Customer\Service\V1\Dto\Customer::CREATED_AT => '2/3/2014',
            \Magento\Customer\Service\V1\Dto\Customer::CREATED_IN => 'Default',
            \Magento\Customer\Service\V1\Dto\Customer::DEFAULT_BILLING => 'test',
            \Magento\Customer\Service\V1\Dto\Customer::DEFAULT_SHIPPING => 'test',
            \Magento\Customer\Service\V1\Dto\Customer::DOB => '2/3/2014',
            \Magento\Customer\Service\V1\Dto\Customer::EMAIL => 'qa@example.com',
            \Magento\Customer\Service\V1\Dto\Customer::FIRSTNAME => 'Joe',
            \Magento\Customer\Service\V1\Dto\Customer::GENDER => 'Male',
            \Magento\Customer\Service\V1\Dto\Customer::GROUP_ID
            => \Magento\Customer\Service\V1\CustomerGroupService::NOT_LOGGED_IN_ID,
            \Magento\Customer\Service\V1\Dto\Customer::LASTNAME => 'Dou',
            \Magento\Customer\Service\V1\Dto\Customer::MIDDLENAME => 'Ivan',
            \Magento\Customer\Service\V1\Dto\Customer::PREFIX => 'Dr.',
            \Magento\Customer\Service\V1\Dto\Customer::STORE_ID => 1,
            \Magento\Customer\Service\V1\Dto\Customer::SUFFIX => 'Jr.',
            \Magento\Customer\Service\V1\Dto\Customer::TAXVAT => 1,
            \Magento\Customer\Service\V1\Dto\Customer::WEBSITE_ID => 1
        ];
    }
}
