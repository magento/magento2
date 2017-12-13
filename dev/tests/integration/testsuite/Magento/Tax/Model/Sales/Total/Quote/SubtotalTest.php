<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model\Sales\Total\Quote;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test \Magento\Tax\Model\Sales\Total\Quote\Subtotal
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SubtotalTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->create(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        );
    }

    protected function getCustomerById($id)
    {
        /**
         * @var $customerRepository \Magento\Customer\Api\CustomerRepositoryInterface
         */
        $customerRepository = $this->objectManager->create(\Magento\Customer\Api\CustomerRepositoryInterface::class);
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
        $customer = $this->objectManager->create(\Magento\Customer\Model\Customer::class)->load($fixtureCustomerId);
        /** @var \Magento\Customer\Model\Group $customerGroup */
        $customerGroup = $this->objectManager->create(\Magento\Customer\Model\Group::class)
            ->load('custom_group', 'customer_group_code');
        $customerGroup->setTaxClassId($customerTaxClassId)->save();
        $customer->setGroupId($customerGroup->getId())->save();

        $productTaxClassId = $this->getProductTaxClassId();
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get('simple');
        $product->setTaxClassId($productTaxClassId)->save();

        $quoteShippingAddressDataObject = $this->getShippingAddressDataObject($fixtureCustomerId);

        /** @var \Magento\Quote\Model\Quote\Address $quoteShippingAddress */
        $quoteShippingAddress = $this->objectManager->create(\Magento\Quote\Model\Quote\Address::class);
        $quoteShippingAddress->importCustomerAddressData($quoteShippingAddressDataObject);
        $quantity = 2;

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
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
        /** @var \Magento\Quote\Model\ShippingAssignment $shippingAssignment */
        $shippingAssignment = $this->objectManager->create(\Magento\Quote\Model\ShippingAssignment::class);
        $shipping = $this->objectManager->create(\Magento\Quote\Model\Shipping::class);
        $shipping->setAddress($address);
        $shippingAssignment->setShipping($shipping);
        $shippingAssignment->setItems($address->getAllItems());
        /** @var  \Magento\Quote\Model\Quote\Address\Total $total */
        $total = $this->objectManager->create(\Magento\Quote\Model\Quote\Address\Total::class);
        /** @var \Magento\Quote\Model\Quote\Address\Total\Subtotal $addressSubtotalCollector */
        $addressSubtotalCollector = $this->objectManager->create(
            \Magento\Quote\Model\Quote\Address\Total\Subtotal::class
        );
        $addressSubtotalCollector->collect($quote, $shippingAssignment, $total);

        /** @var \Magento\Tax\Model\Sales\Total\Quote\Subtotal $subtotalCollector */
        $subtotalCollector = $this->objectManager->create(\Magento\Tax\Model\Sales\Total\Quote\Subtotal::class);
        $subtotalCollector->collect($quote, $shippingAssignment, $total);

        $this->assertEquals($expected['subtotal'], $total->getSubtotal());
        $this->assertEquals($expected['subtotal'] + $expected['tax_amount'], $total->getSubtotalInclTax());
        $this->assertEquals($expected['subtotal'] + $expected['tax_amount'], $address->getBaseSubtotalTotalInclTax());
        $this->assertEquals($expected['discount_amount'], $total->getDiscountAmount());
        $items = $address->getAllItems();
        /** @var \Magento\Quote\Model\Quote\Address\Item $item */
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
        $customer = $this->objectManager->create(\Magento\Customer\Model\Customer::class)->load($fixtureCustomerId);
        /** @var \Magento\Customer\Model\Group $customerGroup */
        $customerGroup = $this->objectManager->create(
            \Magento\Customer\Model\Group::class
        )->load('custom_group', 'customer_group_code');
        $customerGroup->setTaxClassId($customerTaxClassId)->save();
        $customer->setGroupId($customerGroup->getId())->save();

        $productTaxClassId = $this->getProductTaxClassId();
        /** @var \Magento\Catalog\Model\Product $product */
        $childProduct = $this->productRepository->get('simple');
        $childProduct->setTaxClassId($productTaxClassId)->save();
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get('bundle-product');
        $product->setTaxClassId($productTaxClassId)
            ->setPriceType(\Magento\Catalog\Model\Product\Type\AbstractType::CALCULATE_CHILD)
            ->save();

        $quoteShippingAddressDataObject = $this->getShippingAddressDataObject($fixtureCustomerId);

        /** @var \Magento\Quote\Model\Quote\Address $quoteShippingAddress */
        $quoteShippingAddress = $this->objectManager->create(\Magento\Quote\Model\Quote\Address::class);
        $quoteShippingAddress->importCustomerAddressData($quoteShippingAddressDataObject);
        $quantity = 2;

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
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
        /** @var \Magento\Quote\Model\ShippingAssignment $shippingAssignment */
        $shippingAssignment = $this->objectManager->create(\Magento\Quote\Model\ShippingAssignment::class);
        $shipping = $this->objectManager->create(\Magento\Quote\Model\Shipping::class);
        $shipping->setAddress($address);
        $shippingAssignment->setShipping($shipping);
        $shippingAssignment->setItems($quote->getAllItems());
        /** @var  \Magento\Quote\Model\Quote\Address\Total $total */
        $total = $this->objectManager->create(\Magento\Quote\Model\Quote\Address\Total::class);
        /** @var \Magento\Quote\Model\Quote\Address\Total\Subtotal $addressSubtotalCollector */
        $addressSubtotalCollector = $this->objectManager->create(
            \Magento\Quote\Model\Quote\Address\Total\Subtotal::class
        );
        $addressSubtotalCollector->collect($quote, $shippingAssignment, $total);

        /** @var \Magento\Tax\Model\Sales\Total\Quote\Subtotal $subtotalCollector */
        $subtotalCollector = $this->objectManager->create(\Magento\Tax\Model\Sales\Total\Quote\Subtotal::class);
        $subtotalCollector->collect($quote, $shippingAssignment, $total);

        $this->assertEquals($expected['subtotal'], $total->getSubtotal());
        $this->assertEquals($expected['subtotal'] + $expected['tax_amount'], $total->getSubtotalInclTax());
        $this->assertEquals($expected['discount_amount'], $total->getDiscountAmount());
        $items = $address->getAllItems();
        /** @var \Magento\Quote\Model\Quote\Address\Item $item */
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
        $customerTaxClass = $this->objectManager->create(\Magento\Tax\Model\ClassModel::class);
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
        $productTaxClass = $this->objectManager->create(\Magento\Tax\Model\ClassModel::class);
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
        $customerAddress = $this->objectManager->create(
            \Magento\Customer\Model\Address::class
        )->load($fixtureCustomerId);
        /** Set data which corresponds tax class fixture */
        $customerAddress->setCountryId('US')->setRegionId(12)->save();
        /**
         * @var $addressRepository \Magento\Customer\Api\AddressRepositoryInterface
         */
        $addressRepository = $this->objectManager->get(\Magento\Customer\Api\AddressRepositoryInterface::class);
        $quoteShippingAddressDataObject = $addressRepository->getById($fixtureCustomerAddressId);
        return $quoteShippingAddressDataObject;
    }
}
