<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Sales\Total\Quote;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\TotalsCollector;
use Magento\TestFramework\Helper\Bootstrap;

require_once __DIR__ . '/SetupUtil.php';
require_once __DIR__ . '/../../../../_files/tax_calculation_data_aggregated.php';
require_once __DIR__ . '/../../../../_files/full_discount_with_tax.php';

/**
 * Class TaxTest
 *
 * Tests sales taxes with discounts/price rules during checkout.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TaxTest extends \Magento\TestFramework\Indexer\TestCase
{
    /**
     * @var float
     */
    private const EPSILON = 0.0000000001;

    /**
     * Utility object for setting up tax rates, tax classes and tax rules
     *
     * @var SetupUtil
     */
    protected $setupUtil = null;

    /**
     * @var TotalsCollector
     */
    private $totalsCollector;

    /**
     * test setup
     */
    protected function setUp(): void
    {
        /** @var  \Magento\Framework\ObjectManagerInterface $objectManager */
        $objectManager = Bootstrap::getObjectManager();
        $this->totalsCollector = $objectManager->create(TotalsCollector::class);
        $this->setupUtil = new SetupUtil($objectManager);

        parent::setUp();
    }

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
     * @magentoAppIsolation enabled
     */
    public function testCollect()
    {
        /** Preconditions */
        $objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\Tax\Model\ClassModel $customerTaxClass */
        $customerTaxClass = $objectManager->create(\Magento\Tax\Model\ClassModel::class);
        $fixtureCustomerTaxClass = 'CustomerTaxClass2';
        $customerTaxClass->load($fixtureCustomerTaxClass, 'class_name');
        $fixtureCustomerId = 1;
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $objectManager->create(\Magento\Customer\Model\Customer::class)->load($fixtureCustomerId);
        /** @var \Magento\Customer\Model\Group $customerGroup */
        $customerGroup = $objectManager->create(\Magento\Customer\Model\Group::class)
            ->load('custom_group', 'customer_group_code');
        $customerGroup->setTaxClassId($customerTaxClass->getId())->save();
        $customer->setGroupId($customerGroup->getId())->save();

        /** @var \Magento\Tax\Model\ClassModel $productTaxClass */
        $productTaxClass = $objectManager->create(\Magento\Tax\Model\ClassModel::class);
        $fixtureProductTaxClass = 'ProductTaxClass1';
        $productTaxClass->load($fixtureProductTaxClass, 'class_name');
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class)->get('simple');
        $product->setTaxClassId($productTaxClass->getId())->save();

        $fixtureCustomerAddressId = 1;
        $customerAddress = $objectManager->create(\Magento\Customer\Model\Address::class)->load($fixtureCustomerId);
        /** Set data which corresponds tax class fixture */
        $customerAddress->setCountryId('US')->setRegionId(12)->save();
        /** @var \Magento\Quote\Model\Quote\Address $quoteShippingAddress */
        $quoteShippingAddress = $objectManager->create(\Magento\Quote\Model\Quote\Address::class);
        /** @var \Magento\Customer\Api\AddressRepositoryInterface $addressRepository */
        $addressRepository = $objectManager->create(\Magento\Customer\Api\AddressRepositoryInterface::class);
        $quoteShippingAddress->importCustomerAddressData($addressRepository->getById($fixtureCustomerAddressId));

        /** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
        $customerRepository = $objectManager->create(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $objectManager->create(\Magento\Quote\Model\Quote::class);
        $quote->setStoreId(1)
            ->setIsActive(true)
            ->setIsMultiShipping(false)
            ->assignCustomerWithAddressChange($customerRepository->getById($customer->getId()))
            ->setShippingAddress($quoteShippingAddress)
            ->setBillingAddress($quoteShippingAddress)
            ->setCheckoutMethod($customer->getMode())
            ->setPasswordHash($customer->encryptPassword($customer->getPassword()))
            ->addProduct($product->load($product->getId()), 2);

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

    /**
     * Test taxes collection with full discount for quote.
     *
     * Test tax calculation and price when the discount may be bigger than total
     * This method will test the collector through $quote->collectTotals() method
     *
     * @see \Magento\SalesRule\Model\Utility::deltaRoundingFix
     * @magentoDataFixture Magento/Tax/_files/full_discount_with_tax.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testFullDiscountWithDeltaRoundingFix()
    {
        global $fullDiscountIncTax;
        $configData = $fullDiscountIncTax['config_data'];
        $quoteData = $fullDiscountIncTax['quote_data'];
        $expectedResults = $fullDiscountIncTax['expected_result'];

        /** @var  \Magento\Framework\ObjectManagerInterface $objectManager */
        $objectManager = Bootstrap::getObjectManager();

        //Setup tax configurations
        $this->setupUtil = new SetupUtil($objectManager);
        $this->setupUtil->setupTax($configData);

        $quote = $this->setupUtil->setupQuote($quoteData);

        $quote->collectTotals();

        $quoteAddress = $quote->getShippingAddress();

        $this->verifyResult($quoteAddress, $expectedResults);
    }

    /**
     * Verify fields in quote item
     *
     * @param \Magento\Quote\Model\Quote\Address\Item $item
     * @param array $expectedItemData
     * @return $this
     */
    protected function verifyItem($item, $expectedItemData)
    {
        foreach ($expectedItemData as $key => $value) {
            $this->assertEqualsWithDelta(
                $value,
                $item->getData($key),
                self::EPSILON,
                'item ' . $key . ' is incorrect'
            );
        }

        return $this;
    }

    /**
     * Verify one tax rate in a tax row
     *
     * @param array $appliedTaxRate
     * @param array $expectedAppliedTaxRate
     * @return $this
     */
    protected function verifyAppliedTaxRate($appliedTaxRate, $expectedAppliedTaxRate)
    {
        foreach ($expectedAppliedTaxRate as $key => $value) {
            $this->assertEquals($value, $appliedTaxRate[$key], 'Applied tax rate ' . $key . ' is incorrect');
        }
        return $this;
    }

    /**
     * Verify one row in the applied taxes
     *
     * @param array $appliedTax
     * @param array $expectedAppliedTax
     * @return $this
     */
    protected function verifyAppliedTax($appliedTax, $expectedAppliedTax)
    {
        foreach ($expectedAppliedTax as $key => $value) {
            if ($key == 'rates') {
                foreach ($value as $index => $taxRate) {
                    $this->verifyAppliedTaxRate($appliedTax['rates'][$index], $taxRate);
                }
            } else {
                $this->assertEquals($value, $appliedTax[$key], 'Applied tax ' . $key . ' is incorrect');
            }
        }
        return $this;
    }

    /**
     * Verify that applied taxes are correct
     *
     * @param array $appliedTaxes
     * @param array $expectedAppliedTaxes
     * @return $this
     */
    protected function verifyAppliedTaxes($appliedTaxes, $expectedAppliedTaxes)
    {
        foreach ($expectedAppliedTaxes as $taxRateKey => $expectedTaxRate) {
            $this->assertTrue(isset($appliedTaxes[$taxRateKey]), 'Missing tax rate ' . $taxRateKey);
            $this->verifyAppliedTax($appliedTaxes[$taxRateKey], $expectedTaxRate);
        }
        return $this;
    }

    /**
     * Verify fields in quote address
     *
     * @param \Magento\Quote\Model\Quote\Address $quoteAddress
     * @param array $expectedAddressData
     * @return $this
     */
    protected function verifyQuoteAddress($quoteAddress, $expectedAddressData)
    {
        foreach ($expectedAddressData as $key => $value) {
            if ($key == 'applied_taxes') {
                $this->verifyAppliedTaxes($quoteAddress->getAppliedTaxes(), $value);
            } else {
                $this->assertEqualsWithDelta(
                    $value,
                    $quoteAddress->getData($key),
                    self::EPSILON,
                    'Quote address ' . $key . ' is incorrect'
                );
            }
        }

        return $this;
    }

    /**
     * Verify fields in quote address and quote item are correct
     *
     * @param \Magento\Quote\Model\Quote\Address $quoteAddress
     * @param array $expectedResults
     * @return $this
     */
    protected function verifyResult($quoteAddress, $expectedResults)
    {
        $addressData = $expectedResults['address_data'];

        $this->verifyQuoteAddress($quoteAddress, $addressData);

        $quoteItems = $quoteAddress->getAllItems();
        foreach ($quoteItems as $item) {
            /** @var  \Magento\Quote\Model\Quote\Address\Item $item */
            $sku = $item->getProduct()->getSku();
            $expectedItemData = $expectedResults['items_data'][$sku];
            $this->verifyItem($item, $expectedItemData);
        }

        return $this;
    }

    /**
     * Test tax calculation with various configuration and combination of items
     * This method will test various collectors through $quoteAddress->collectTotals() method
     *
     * @param array $configData
     * @param array $quoteData
     * @param array $expectedResults
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @dataProvider taxDataProvider
     * @return void
     */
    public function testTaxCalculation($configData, $quoteData, $expectedResults)
    {
        $db = \Magento\TestFramework\Helper\Bootstrap::getInstance()->getBootstrap()
            ->getApplication()
            ->getDbInstance();
        if (!$db->isDbDumpExists()) {
            throw new \LogicException('DB dump does not exist.');
        }
        $db->restoreFromDbDump();
        //Setup tax configurations
        $this->setupUtil->setupTax($configData);

        $quote = $this->setupUtil->setupQuote($quoteData);
        $quoteAddress = $quote->getShippingAddress();
        $this->totalsCollector->collectAddressTotals($quote, $quoteAddress);
        $this->verifyResult($quoteAddress, $expectedResults);

        $skus = array_map(function ($item) {
            return $item['sku'];
        }, $quoteData['items'] ?? []);
        $this->removeProducts($skus);
    }

    /**
     * Read the array defined in ../../../../_files/tax_calculation_data_aggregated.php
     * and feed it to testTaxCalculation
     *
     * @return array
     */
    public function taxDataProvider()
    {
        global $taxCalculationData;
        return $taxCalculationData;
    }

    /**
     * Cleanup test by removing products.
     *
     * @param string[] $skus
     * @return void
     */
    private function removeProducts(array $skus): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $objectManager->create(ProductRepositoryInterface::class);
        $registry = $objectManager->get(\Magento\Framework\Registry::class);
        /** @var ProductRepositoryInterface $productRepository */
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);

        foreach ($skus as $sku) {
            try {
                $productRepository->deleteById($sku);
            } catch (NoSuchEntityException $e) {
                // product already deleted
            }
        }

        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
    }
}
