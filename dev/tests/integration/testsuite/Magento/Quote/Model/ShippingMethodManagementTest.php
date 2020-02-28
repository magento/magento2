<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Vat;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Api\Data\TaxClassInterface;
use Magento\Tax\Api\TaxClassRepositoryInterface;
use Magento\Tax\Model\ClassModel;
use Magento\Tax\Model\Config as TaxConfig;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Quote\Model\GetQuoteByReservedOrderId;

/**
 * Test for shipping methods management
 *
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShippingMethodManagementTest extends \PHPUnit\Framework\TestCase
{
    /** @var ObjectManagerInterface $objectManager */
    private $objectManager;

    /** @var GroupRepositoryInterface $groupRepository */
    private $groupRepository;

    /** @var TaxClassRepositoryInterface $taxClassRepository */
    private $taxClassRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->groupRepository = $this->objectManager->get(GroupRepositoryInterface::class);
        $this->taxClassRepository = $this->objectManager->get(TaxClassRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Magento/SalesRule/_files/cart_rule_100_percent_off.php
     * @magentoDataFixture Magento/Sales/_files/quote_with_customer.php
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testRateAppliedToShipping(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\Quote\Api\CartRepositoryInterface $quoteRepository */
        $quoteRepository = $objectManager->create(\Magento\Quote\Api\CartRepositoryInterface::class);
        $customerQuote = $quoteRepository->getForCustomer(1);
        $this->assertEquals(0, $customerQuote->getBaseGrandTotal());
    }

    /**
     * @magentoConfigFixture current_store carriers/tablerate/active 1
     * @magentoConfigFixture current_store carriers/flatrate/active 0
     * @magentoConfigFixture current_store carriers/freeshipping/active 0
     * @magentoConfigFixture current_store carriers/tablerate/condition_name package_qty
     * @magentoDataFixture Magento/SalesRule/_files/cart_rule_free_shipping_by_cart.php
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoDataFixture Magento/OfflineShipping/_files/tablerates.php
     * @return void
     */
    public function testTableRateFreeShipping()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $objectManager->get(\Magento\Quote\Model\Quote::class);
        $quote->load('test01', 'reserved_order_id');
        $cartId = $quote->getId();
        if (!$cartId) {
            $this->fail('quote fixture failed');
        }
        /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
        $quoteIdMask = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Quote\Model\QuoteIdMaskFactory::class)
            ->create();
        $quoteIdMask->load($cartId, 'quote_id');
        //Use masked cart Id
        $cartId = $quoteIdMask->getMaskedId();
        $data = [
            'data' => [
                'country_id' => "US",
                'postcode' => null,
                'region' => null,
                'region_id' => null
            ]
        ];
        /** @var \Magento\Quote\Api\Data\EstimateAddressInterface $address */
        $address = $objectManager->create(\Magento\Quote\Api\Data\EstimateAddressInterface::class, $data);
        /** @var  \Magento\Quote\Api\GuestShippingMethodManagementInterface $shippingEstimation */
        $shippingEstimation = $objectManager->get(\Magento\Quote\Api\GuestShippingMethodManagementInterface::class);
        $result = $shippingEstimation->estimateByAddress($cartId, $address);
        $this->assertNotEmpty($result);
        $expectedResult = [
            'method_code' => 'bestway',
            'amount' => 0
        ];
        foreach ($result as $rate) {
            $this->assertEquals($expectedResult['amount'], $rate->getAmount());
            $this->assertEquals($expectedResult['method_code'], $rate->getMethodCode());
        }
    }

    /**
     * Test table rate amount for the cart that contains some items with free shipping applied.
     *
     * @magentoConfigFixture current_store carriers/tablerate/active 1
     * @magentoConfigFixture current_store carriers/flatrate/active 0
     * @magentoConfigFixture current_store carriers/freeshipping/active 0
     * @magentoConfigFixture current_store carriers/tablerate/condition_name package_value_with_discount
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     * @magentoDataFixture Magento/SalesRule/_files/cart_rule_free_shipping_by_category.php
     * @magentoDataFixture Magento/Sales/_files/quote_with_multiple_products.php
     * @magentoDataFixture Magento/OfflineShipping/_files/tablerates_price.php
     * @return void
     */
    public function testTableRateWithCartRuleForFreeShipping()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $quote = $this->getQuote('tableRate');
        $cartId = $quote->getId();
        if (!$cartId) {
            $this->fail('quote fixture failed');
        }
        /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
        $quoteIdMask = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Quote\Model\QuoteIdMaskFactory::class)
            ->create();
        $quoteIdMask->load($cartId, 'quote_id');
        //Use masked cart Id
        $cartId = $quoteIdMask->getMaskedId();
        $addressFactory = $this->objectManager->get(\Magento\Quote\Api\Data\AddressInterfaceFactory::class);
        /** @var \Magento\Quote\Api\Data\AddressInterface $address */
        $address = $addressFactory->create();
        $address->setCountryId('US');
        /** @var  \Magento\Quote\Api\GuestShippingMethodManagementInterface $shippingEstimation */
        $shippingEstimation = $objectManager->get(\Magento\Quote\Api\GuestShippingMethodManagementInterface::class);
        $result = $shippingEstimation->estimateByExtendedAddress($cartId, $address);
        $this->assertCount(1, $result);
        $rate = reset($result);
        $expectedResult = [
                'method_code' => 'bestway',
                'amount' => 10
        ];
        $this->assertEquals($expectedResult['method_code'], $rate->getMethodCode());
        $this->assertEquals($expectedResult['amount'], $rate->getAmount());
    }

    /**
     * Retrieves quote by reserved order id.
     *
     * @param string $reservedOrderId
     * @return Quote
     */
    private function getQuote(string $reservedOrderId): Quote
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('reserved_order_id', $reservedOrderId)
            ->create();

        /** @var CartRepositoryInterface $quoteRepository */
        $quoteRepository = $this->objectManager->get(CartRepositoryInterface::class);
        $items = $quoteRepository->getList($searchCriteria)->getItems();

        return array_pop($items);
    }

    /**
     * @magentoConfigFixture current_store carriers/tablerate/active 1
     * @magentoConfigFixture current_store carriers/tablerate/condition_name package_qty
     * @magentoDataFixture Magento/SalesRule/_files/cart_rule_free_shipping.php
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoDataFixture Magento/OfflineShipping/_files/tablerates.php
     * @return void
     */
    public function testEstimateByAddressWithCartPriceRuleByItem()
    {
        $this->executeTestFlow(0, 0);
    }

    /**
     * @magentoConfigFixture current_store carriers/tablerate/active 1
     * @magentoConfigFixture current_store carriers/tablerate/condition_name package_qty
     * @magentoDataFixture Magento/SalesRule/_files/cart_rule_free_shipping_by_cart.php
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoDataFixture Magento/OfflineShipping/_files/tablerates.php
     * @return void
     */
    public function testEstimateByAddressWithCartPriceRuleByShipment()
    {
        $this->markTestSkipped('According to MAGETWO-69940 it is an incorrect behavior');
        // Rule applied to entire shipment should not overwrite flat or table rate shipping prices
        // Only rules applied to specific items should modify those prices (MAGETWO-63844)
        $this->executeTestFlow(5, 10);
    }

    /**
     * @magentoConfigFixture current_store carriers/tablerate/active 1
     * @magentoConfigFixture current_store carriers/tablerate/condition_name package_qty
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoDataFixture Magento/OfflineShipping/_files/tablerates.php
     * @return void
     */
    public function testEstimateByAddress()
    {
        $this->executeTestFlow(5, 10);
    }

    /**
     * Provide testing of shipping method estimation based on address
     *
     * @param int $flatRateAmount
     * @param int $tableRateAmount
     * @return void
     */
    private function executeTestFlow($flatRateAmount, $tableRateAmount)
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $objectManager->get(\Magento\Quote\Model\Quote::class);
        $quote->load('test01', 'reserved_order_id');
        $cartId = $quote->getId();
        if (!$cartId) {
            $this->fail('quote fixture failed');
        }
        /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
        $quoteIdMask = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Quote\Model\QuoteIdMaskFactory::class)
            ->create();
        $quoteIdMask->load($cartId, 'quote_id');
        //Use masked cart Id
        $cartId = $quoteIdMask->getMaskedId();
        $data = [
            'data' => [
                'country_id' => "US",
                'postcode' => null,
                'region' => null,
                'region_id' => null
            ]
        ];
        /** @var \Magento\Quote\Api\Data\EstimateAddressInterface $address */
        $address = $objectManager->create(\Magento\Quote\Api\Data\EstimateAddressInterface::class, $data);
        /** @var  \Magento\Quote\Api\GuestShippingMethodManagementInterface $shippingEstimation */
        $shippingEstimation = $objectManager->get(\Magento\Quote\Api\GuestShippingMethodManagementInterface::class);
        $result = $shippingEstimation->estimateByAddress($cartId, $address);
        $this->assertNotEmpty($result);
        $expectedResult = [
            'tablerate' => [
                    'method_code' => 'bestway',
                    'amount' => $tableRateAmount
                ],
            'flatrate' => [
                'method_code' => 'flatrate',
                'amount' => $flatRateAmount
            ]
        ];
        foreach ($result as $rate) {
            $this->assertEquals($expectedResult[$rate->getCarrierCode()]['amount'], $rate->getAmount());
            $this->assertEquals($expectedResult[$rate->getCarrierCode()]['method_code'], $rate->getMethodCode());
        }
    }

    /**
     * Test for estimate shipping with tax and changed VAT customer group
     *
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Tax/_files/tax_classes_de.php
     * @magentoDataFixture Magento/Sales/_files/quote_with_customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_group.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoConfigFixture current_store customer/create_account/tax_calculation_address_type shipping
     * @magentoConfigFixture current_store customer/create_account/default_group 1
     * @magentoConfigFixture current_store customer/create_account/auto_group_assign 1
     * @magentoConfigFixture current_store tax/calculation/price_includes_tax 1
     * @magentoConfigFixture current_store tax/calculation/shipping_includes_tax 1
     */
    public function testEstimateByAddressWithInclExclTaxAndVATGroup()
    {
        $this->markTestSkipped('MC-30817');
        /** @var CustomerRepositoryInterface $customerRepository */
        $customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
        $customer = $customerRepository->get('customer@example.com');

        /** @var GroupInterface $customerGroup */
        $customerGroup = $this->findCustomerGroupByCode('custom_group');
        $customerGroup->setTaxClassId($this->getTaxClass('CustomerTaxClass')->getClassId());
        $this->groupRepository->save($customerGroup);

        $customer->setGroupId($customerGroup->getId());
        $customer->setTaxvat('12');
        $customerRepository->save($customer);
        $this->setConfig($customerGroup->getId(), $this->getTaxClass('ProductTaxClass')->getClassId());
        $this->changeCustomerAddress($customer->getDefaultShipping());

        $quote = $this->objectManager->get(GetQuoteByReservedOrderId::class)->execute('test01');

        /** @var ShippingMethodManagementInterface $shippingEstimation */
        $shippingEstimation = $this->objectManager->get(ShippingMethodManagementInterface::class);
        $result = $shippingEstimation->estimateByAddressId($quote->getId(), $customer->getDefaultShipping());

        $this->assertEquals(6.05, $result[0]->getPriceInclTax());
        $this->assertEquals(5.0, $result[0]->getPriceExclTax());
    }

    /**
     * Find the group with a given code.
     *
     * @param string $code
     *
     * @return GroupInterface
     */
    protected function findCustomerGroupByCode(string $code): ?GroupInterface
    {
        /** @var SearchCriteriaBuilder $searchBuilder */
        $searchBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchBuilder->addFilter('code', $code)
            ->create();
        $groups = $this->groupRepository->getList($searchCriteria)
            ->getItems();

        return array_shift($groups);
    }

    /**
     * Change customer address
     *
     * @param int $customerAddressId
     *
     * @return AddressInterface
     */
    private function changeCustomerAddress(int $customerAddressId): AddressInterface
    {
        $addressRepository = $this->objectManager->get(AddressRepositoryInterface::class);
        $address = $addressRepository->getById($customerAddressId);
        $address->setVatId(12345);
        $address->setCountryId('DE');
        $address->setRegionId(0);
        $address->setPostcode(10178);

        return $addressRepository->save($address);
    }

    /**
     * Get tax class.
     *
     * @param string $name
     *
     * @return TaxClassInterface
     */
    private function getTaxClass(string $name): ?TaxClassInterface
    {
        /** @var SearchCriteriaBuilder $searchBuilder */
        $searchBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchBuilder->addFilter(ClassModel::KEY_NAME, $name)
            ->create();
        $searchResults = $this->taxClassRepository->getList($searchCriteria)
            ->getItems();

        return array_shift($searchResults);
    }

    /**
     * Set the configuration.
     *
     * @param int $customerGroupId
     * @param int $productTaxClassId
     *
     * @return void
     */
    private function setConfig(int $customerGroupId, int $productTaxClassId): void
    {
        $configData = [
            [
                'path' => Vat::XML_PATH_CUSTOMER_VIV_INVALID_GROUP,
                'value' => $customerGroupId,
                'scope' => ScopeInterface::SCOPE_STORE,
            ],
            [
                'path' => TaxConfig::CONFIG_XML_PATH_SHIPPING_TAX_CLASS,
                'value' => $productTaxClassId,
                'scope' => ScopeInterface::SCOPE_STORE,
            ],
        ];
        $config = $this->objectManager->get(MutableScopeConfigInterface::class);
        foreach ($configData as $data) {
            $config->setValue($data['path'], $data['value'], $data['scope']);
        }
    }
}
