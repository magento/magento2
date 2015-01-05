<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Tax\Model\Sales\Total\Quote;

use Magento\Tax\Model\ClassModel;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test \Magento\Tax\Model\Sales\Total\Quote\Subtotal
 */
class SubtotalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Builder
     *
     * @var \Magento\Customer\Api\Data\CustomerDataBuilder
     */
    private $customerBuilder;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->customerBuilder = $this->objectManager->create('Magento\Customer\Api\Data\CustomerDataBuilder');
    }

    protected function getCustomerById($id)
    {
        /**
         * @var $customerRepository \Magento\Customer\Api\CustomerRepositoryInterface
         */
        $customerRepository = $this->objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');
        return $customerRepository->getById($id);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoDataFixture Magento/Tax/_files/tax_classes.php
     * @magentoDataFixture Magento/Customer/_files/customer_group.php
     * @magentoDataFixture Magento/Catalog/_files/products.php
     * @magentoConfigFixture current_store tax/calculation/algorithm UNIT_BASE_CALCULATION
     * @dataProvider collectUnitBasedDataProvider
     */
    public function testCollectUnitBased($expected)
    {
        $customerTaxClassId = $this->getCustomerTaxClassId();
        $fixtureCustomerId = 1;
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $this->objectManager->create('Magento\Customer\Model\Customer')->load($fixtureCustomerId);
        /** @var \Magento\Customer\Model\Group $customerGroup */
        $customerGroup = $this->objectManager->create(
            'Magento\Customer\Model\Group'
        )->load(
                'custom_group',
                'customer_group_code'
            );
        $customerGroup->setTaxClassId($customerTaxClassId)->save();
        $customer->setGroupId($customerGroup->getId())->save();

        $productTaxClassId = $this->getProductTaxClassId();
        $fixtureProductId = 1;
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->objectManager->create('Magento\Catalog\Model\Product')->load($fixtureProductId);
        $product->setTaxClassId($productTaxClassId)->save();

        $quoteShippingAddressDataObject = $this->getShippingAddressDataObject($fixtureCustomerId);

        /** @var \Magento\Sales\Model\Quote\Address $quoteShippingAddress */
        $quoteShippingAddress = $this->objectManager->create('Magento\Sales\Model\Quote\Address');
        $quoteShippingAddress->importCustomerAddressData($quoteShippingAddressDataObject);
        $quantity = 2;

        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $this->objectManager->create('Magento\Sales\Model\Quote');
        $quote->setStoreId(
                1
            )->setIsActive(
                true
            )->setIsMultiShipping(
                false
            )->assignCustomerWithAddressChange(
                $this->getCustomerById($customer->getId())
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
                $quantity
            );
        $address = $quote->getShippingAddress();

        /** @var \Magento\Sales\Model\Quote\Address\Total\Subtotal $addressSubtotalCollector */
        $addressSubtotalCollector = $this->objectManager->create('Magento\Sales\Model\Quote\Address\Total\Subtotal');
        $addressSubtotalCollector->collect($address);

        /** @var \Magento\Tax\Model\Sales\Total\Quote\Subtotal $subtotalCollector */
        $subtotalCollector = $this->objectManager->create('Magento\Tax\Model\Sales\Total\Quote\Subtotal');
        $subtotalCollector->collect($address);

        $this->assertEquals($expected['subtotal'], $address->getSubtotal());
        $this->assertEquals($expected['subtotal'] + $expected['tax_amount'], $address->getSubtotalInclTax());
        $this->assertEquals($expected['discount_amount'], $address->getDiscountAmount());
        $items = $address->getAllItems();
        /** @var \Magento\Sales\Model\Quote\Address\Item $item */
        $item = $items[0];
        $this->assertEquals($expected['items'][0]['price'], $item->getPrice());
        $this->assertEquals($expected['items'][0]['price_incl_tax'], $item->getPriceInclTax());
        $this->assertEquals($expected['items'][0]['row_total'], $item->getRowTotal());
        $this->assertEquals($expected['items'][0]['row_total_incl_tax'], $item->getRowTotalInclTax());
        $this->assertEquals($expected['items'][0]['tax_percent'], $item->getTaxPercent());
    }

    public function collectUnitBasedDataProvider()
    {
        return [
            'one_item' => [
                [
                    'subtotal' => 20,
                    'tax_amount' => 1.5,
                    'discount_amount' => 0,
                    'items' => [
                        [
                            'tax_amount' => 1.5,
                            'price' => 10,
                            'price_incl_tax' => 10.75,
                            'row_total' => 20,
                            'row_total_incl_tax' => 21.5,
                            'taxable_amount' => 10,
                            'code' => 'simple',
                            'type' => 'product',
                            'tax_percent' => 7.5,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoDataFixture Magento/Tax/_files/tax_classes.php
     * @magentoDataFixture Magento/Customer/_files/customer_group.php
     * @magentoDataFixture Magento/Bundle/_files/product.php
     * @magentoConfigFixture current_store tax/calculation/algorithm UNIT_BASE_CALCULATION
     * @dataProvider collectUnitBasedDataProvider
     */
    public function testCollectUnitBasedBundleProduct($expected)
    {
        $customerTaxClassId = $this->getCustomerTaxClassId();
        $fixtureCustomerId = 1;
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $this->objectManager->create('Magento\Customer\Model\Customer')->load($fixtureCustomerId);
        /** @var \Magento\Customer\Model\Group $customerGroup */
        $customerGroup = $this->objectManager->create(
            'Magento\Customer\Model\Group'
        )->load(
                'custom_group',
                'customer_group_code'
            );
        $customerGroup->setTaxClassId($customerTaxClassId)->save();
        $customer->setGroupId($customerGroup->getId())->save();

        $productTaxClassId = $this->getProductTaxClassId();
        $fixtureChildProductId = 1;
        /** @var \Magento\Catalog\Model\Product $product */
        $childProduct = $this->objectManager->create('Magento\Catalog\Model\Product')->load($fixtureChildProductId);
        $childProduct->setTaxClassId($productTaxClassId)->save();
        $fixtureProductId = 3;
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->objectManager->create('Magento\Catalog\Model\Product')->load($fixtureProductId);
        $product->setTaxClassId($productTaxClassId)
            ->setPriceType(\Magento\Catalog\Model\Product\Type\AbstractType::CALCULATE_CHILD)
            ->save();

        $quoteShippingAddressDataObject = $this->getShippingAddressDataObject($fixtureCustomerId);

        /** @var \Magento\Sales\Model\Quote\Address $quoteShippingAddress */
        $quoteShippingAddress = $this->objectManager->create('Magento\Sales\Model\Quote\Address');
        $quoteShippingAddress->importCustomerAddressData($quoteShippingAddressDataObject);
        $quantity = 2;

        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $this->objectManager->create('Magento\Sales\Model\Quote');
        $quote->setStoreId(
                1
            )->setIsActive(
                true
            )->setIsMultiShipping(
                false
            )->assignCustomerWithAddressChange(
                $this->getCustomerById($customer->getId())
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
                $quantity
            );
        $address = $quote->getShippingAddress();

        /** @var \Magento\Sales\Model\Quote\Address\Total\Subtotal $addressSubtotalCollector */
        $addressSubtotalCollector = $this->objectManager->create('Magento\Sales\Model\Quote\Address\Total\Subtotal');
        $addressSubtotalCollector->collect($address);

        /** @var \Magento\Tax\Model\Sales\Total\Quote\Subtotal $subtotalCollector */
        $subtotalCollector = $this->objectManager->create('Magento\Tax\Model\Sales\Total\Quote\Subtotal');
        $subtotalCollector->collect($address);

        $this->assertEquals($expected['subtotal'], $address->getSubtotal());
        $this->assertEquals($expected['subtotal'] + $expected['tax_amount'], $address->getSubtotalInclTax());
        $this->assertEquals($expected['discount_amount'], $address->getDiscountAmount());
        $items = $address->getAllItems();
        /** @var \Magento\Sales\Model\Quote\Address\Item $item */
        $item = $items[0];
        $this->assertEquals($expected['items'][0]['price'], $item->getPrice());
        $this->assertEquals($expected['items'][0]['price_incl_tax'], $item->getPriceInclTax());
        $this->assertEquals($expected['items'][0]['row_total'], $item->getRowTotal());
        $this->assertEquals($expected['items'][0]['row_total_incl_tax'], $item->getRowTotalInclTax());
    }

    /**
     * Get customer tax class id
     *
     * @return int
     */
    protected function getCustomerTaxClassId()
    {
        $customerTaxClass = $this->objectManager->create('Magento\Tax\Model\ClassModel');
        $fixtureCustomerTaxClass = 'CustomerTaxClass2';
        /** @var \Magento\Tax\Model\ClassModel $customerTaxClass */
        $customerTaxClass->load($fixtureCustomerTaxClass, 'class_name');
        return $customerTaxClass->getId();
    }

    /**
     * Get product tax class id
     *
     * @return int
     */
    protected function getProductTaxClassId()
    {
        /** @var \Magento\Tax\Model\ClassModel $productTaxClass */
        $productTaxClass = $this->objectManager->create('Magento\Tax\Model\ClassModel');
        $fixtureProductTaxClass = 'ProductTaxClass1';
        $productTaxClass->load($fixtureProductTaxClass, 'class_name');
        return $productTaxClass->getId();
    }

    /**
     * @param $fixtureCustomerId
     * @return \Magento\Customer\Api\Data\AddressInterface
     */
    protected function getShippingAddressDataObject($fixtureCustomerId)
    {
        $fixtureCustomerAddressId = 1;
        $customerAddress = $this->objectManager->create('Magento\Customer\Model\Address')->load($fixtureCustomerId);
        /** Set data which corresponds tax class fixture */
        $customerAddress->setCountryId('US')->setRegionId(12)->save();
        /**
         * @var $addressRepository \Magento\Customer\Api\AddressRepositoryInterface
         */
        $addressRepository = $this->objectManager->get('Magento\Customer\Api\AddressRepositoryInterface');
        $quoteShippingAddressDataObject = $addressRepository->getById($fixtureCustomerAddressId);
        return $quoteShippingAddressDataObject;
    }
}
