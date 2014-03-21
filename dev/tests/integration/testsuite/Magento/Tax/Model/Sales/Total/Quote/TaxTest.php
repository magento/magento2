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
namespace Magento\Tax\Model\Sales\Total\Quote;

use Magento\TestFramework\Helper\Bootstrap;

class TaxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test taxes collection for quote.
     *
     * Quote has customer and product.
     * Product tax class and customer group tax class along with billing address have corresponding tax rule.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoDataFixture Magento/Catalog/_files/products.php
     * @magentoDataFixture Magento/Tax/_files/tax_classes.php
     * @magentoDataFixture Magento/Customer/_files/customer_group.php
     * @magentoDbIsolation enabled
     */
    public function testCollect()
    {
        /** Preconditions */
        $objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\Tax\Model\ClassModel $customerTaxClass */
        $customerTaxClass = $objectManager->create('Magento\Tax\Model\ClassModel');
        $fixtureCustomerTaxClass = 'CustomerTaxClass2';
        $customerTaxClass->load($fixtureCustomerTaxClass, 'class_name');
        $fixtureCustomerId = 1;
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $objectManager->create('Magento\Customer\Model\Customer')->load($fixtureCustomerId);
        /** @var \Magento\Customer\Model\Group $customerGroup */
        $customerGroup = $objectManager->create(
            'Magento\Customer\Model\Group'
        )->load(
            'custom_group',
            'customer_group_code'
        );
        $customerGroup->setTaxClassId($customerTaxClass->getId())->save();
        $customer->setGroupId($customerGroup->getId())->save();

        /** @var \Magento\Tax\Model\ClassModel $productTaxClass */
        $productTaxClass = $objectManager->create('Magento\Tax\Model\ClassModel');
        $fixtureProductTaxClass = 'ProductTaxClass1';
        $productTaxClass->load($fixtureProductTaxClass, 'class_name');
        $fixtureProductId = 1;
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $objectManager->create('Magento\Catalog\Model\Product')->load($fixtureProductId);
        $product->setTaxClassId($productTaxClass->getId())->save();

        $fixtureCustomerAddressId = 1;
        $customerAddress = $objectManager->create('Magento\Customer\Model\Address')->load($fixtureCustomerId);
        /** Set data which corresponds tax class fixture */
        $customerAddress->setCountryId('US')->setRegionId(12)->save();
        /** @var \Magento\Sales\Model\Quote\Address $quoteShippingAddress */
        $quoteShippingAddress = $objectManager->create('Magento\Sales\Model\Quote\Address');
        /** @var \Magento\Customer\Service\V1\CustomerAddressServiceInterface $addressService */
        $addressService = $objectManager->create('Magento\Customer\Service\V1\CustomerAddressServiceInterface');
        $quoteShippingAddress->importCustomerAddressData($addressService->getAddress($fixtureCustomerAddressId));

        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $objectManager->create('Magento\Sales\Model\Quote');
        $quote->setStoreId(
            1
        )->setIsActive(
            true
        )->setIsMultiShipping(
            false
        )->assignCustomerWithAddressChange(
            $customer
        )->setShippingAddress(
            $quoteShippingAddress
        )->setBillingAddress(
            $quoteShippingAddress
        )->setCheckoutMethod(
            $customer->getMode()
        )->setPasswordHash(
            $customer->encryptPassword($customer->getPassword())
        )->addProduct(
            $product->load($product->getId()),
            2
        );

        /**
         * Execute SUT.
         * \Magento\Tax\Model\Sales\Total\Quote\Tax::collect cannot be called separately from
         * \Magento\Tax\Model\Sales\Total\Quote\Subtotal::collect because tax to zero amount will be applied.
         * That is why it make sense to call collectTotals() instead, which will call SUT in its turn.
         */
        $quote->collectTotals();

        /** Check results */
        $this->assertEquals(
            $customerTaxClass->getId(),
            $quote->getCustomerTaxClassId(),
            'Customer tax class ID in quote is invalid.'
        );
        $this->assertEquals(
            21.5,
            $quote->getGrandTotal(),
            'Customer tax was collected by \Magento\Tax\Model\Sales\Total\Quote\Tax::collect incorrectly.'
        );
    }
}
