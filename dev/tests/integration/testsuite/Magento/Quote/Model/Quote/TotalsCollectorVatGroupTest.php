<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Quote;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\GroupInterfaceFactory;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\Vat;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Observer\Frontend\Quote\Address\CollectTotalsObserver;
use Magento\Quote\Observer\Frontend\Quote\Address\VatValidator;
use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Api\Data\TaxClassInterfaceFactory;
use Magento\Tax\Api\Data\TaxRateInterfaceFactory;
use Magento\Tax\Api\Data\TaxRuleInterfaceFactory;
use Magento\Tax\Api\TaxClassManagementInterface;
use Magento\Tax\Api\TaxClassRepositoryInterface;
use Magento\Tax\Api\TaxRateRepositoryInterface;
use Magento\Tax\Api\TaxRuleRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Tax of a quote after the customer switches from an address with a valid EU VAT ID to one without it.
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TotalsCollectorVatGroupTest extends TestCase
{
    private const RETAIL_CUSTOMER_TAX_CLASS_ID = 3;
    private const TAXABLE_GOODS_TAX_CLASS_ID = 2;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var int
     */
    private $intraUnionGroupId;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->mockVatValidation();
        $this->createTaxSetup();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->objectManager->removeSharedInstance(Vat::class);
        $this->objectManager->removeSharedInstance(VatValidator::class);
        $this->objectManager->removeSharedInstance(CollectTotalsObserver::class);
        $this->objectManager->get(CustomerSession::class)->setCustomerGroupId(null);
    }

    /**
     * @magentoConfigFixture current_store customer/create_account/auto_group_assign 1
     * @magentoConfigFixture current_store customer/create_account/viv_on_each_transaction 1
     * @magentoConfigFixture current_store customer/create_account/default_group 1
     * @magentoConfigFixture current_store customer/create_account/viv_domestic_group 1
     * @magentoConfigFixture current_store customer/create_account/viv_invalid_group 1
     * @magentoConfigFixture current_store customer/create_account/viv_error_group 1
     * @magentoConfigFixture current_store general/store_information/country_id FR
     * @magentoConfigFixture current_store tax/calculation/based_on shipping
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Catalog/_files/products.php
     * @param string $vatAddressType
     * @param bool $isCustomerInIntraUnionGroup
     * @return void
     */
    #[DataProvider('addressTypeDataProvider')]
    public function testAddressWithoutVatIdIsTaxedAfterAddressWithValidVatId(
        string $vatAddressType,
        bool $isCustomerInIntraUnionGroup
    ): void {
        $this->objectManager->get(MutableScopeConfigInterface::class)->setValue(
            'customer/create_account/tax_calculation_address_type',
            $vatAddressType,
            ScopeInterface::SCOPE_STORE,
            'default'
        );
        if ($isCustomerInIntraUnionGroup) {
            $this->moveCustomerToGroup($this->intraUnionGroupId);
        }

        $quote = $this->createCustomerQuote();
        $this->setAddresses($quote, $this->getBelgianAddressData());
        $quote->collectTotals();
        $this->objectManager->get(CartRepositoryInterface::class)->save($quote);
        $this->assertEquals(0, (float)$quote->getShippingAddress()->getTaxAmount());

        $quote = $this->objectManager->get(QuoteFactory::class)->create()->load($quote->getId());
        $this->setAddresses($quote, $this->getFrenchAddressData());
        $quote->collectTotals();

        $this->assertEquals(2.0, (float)$quote->getShippingAddress()->getTaxAmount());
    }

    /**
     * @magentoConfigFixture current_store customer/create_account/auto_group_assign 1
     * @magentoConfigFixture current_store customer/create_account/viv_on_each_transaction 1
     * @magentoConfigFixture current_store customer/create_account/tax_calculation_address_type shipping
     * @magentoConfigFixture current_store customer/create_account/default_group 1
     * @magentoConfigFixture current_store customer/create_account/viv_domestic_group 1
     * @magentoConfigFixture current_store customer/create_account/viv_invalid_group 1
     * @magentoConfigFixture current_store customer/create_account/viv_error_group 1
     * @magentoConfigFixture current_store general/store_information/country_id FR
     * @magentoConfigFixture current_store tax/calculation/based_on shipping
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Catalog/_files/products.php
     * @return void
     */
    public function testShippingAddressWithValidVatIdStaysTaxFreeWithBillingAddressWithoutVatId(): void
    {
        $quote = $this->createCustomerQuote();
        $quote->getBillingAddress()->addData($this->getFrenchAddressData());
        $quote->getShippingAddress()->addData($this->getBelgianAddressData())->setCollectShippingRates(false);
        $quote->collectTotals();

        $this->assertEquals(0, (float)$quote->getShippingAddress()->getTaxAmount());
    }

    /**
     * VAT address type and whether the customer account is already in the intra-union group
     *
     * @return array
     */
    public static function addressTypeDataProvider(): array
    {
        return [
            'billing address, customer in the default group' => ['billing', false],
            'billing address, customer in the intra-union group' => ['billing', true],
            'shipping address, customer in the default group' => ['shipping', false],
            'shipping address, customer in the intra-union group' => ['shipping', true],
        ];
    }

    /**
     * Put customer@example.com into a group, as a saved default address with a valid VAT ID does
     *
     * @param int $groupId
     * @return void
     */
    private function moveCustomerToGroup(int $groupId): void
    {
        $customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
        $customer = $customerRepository->get('customer@example.com');
        $customer->setGroupId($groupId);
        $customerRepository->save($customer);
    }

    /**
     * Create a quote of customer@example.com with one simple product of price 10
     *
     * @return Quote
     */
    private function createCustomerQuote(): Quote
    {
        $customer = $this->objectManager->get(CustomerRepositoryInterface::class)->get('customer@example.com');
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get('simple');
        $product->setTaxClassId(self::TAXABLE_GOODS_TAX_CLASS_ID);
        $product = $productRepository->save($product);

        /** @var Quote $quote */
        $quote = $this->objectManager->get(QuoteFactory::class)->create();
        $quote->setStoreId(1)->setIsActive(true);
        $quote->assignCustomer($customer);
        $quote->addProduct($product, 1);

        return $quote;
    }

    /**
     * Set the same address data as billing and shipping address
     *
     * @param Quote $quote
     * @param array $addressData
     * @return void
     */
    private function setAddresses(Quote $quote, array $addressData): void
    {
        $quote->getBillingAddress()->addData($addressData);
        $quote->getShippingAddress()->addData($addressData)->setCollectShippingRates(false);
    }

    /**
     * Address with a valid intra-union VAT ID
     *
     * @return array
     */
    private function getBelgianAddressData(): array
    {
        return [
            'firstname' => 'John',
            'lastname' => 'Smith',
            'street' => 'Rue du Moulin 13',
            'city' => 'Bruxelles',
            'postcode' => '1000',
            'country_id' => 'BE',
            'telephone' => '0123456789',
            'vat_id' => 'BE0834968971',
        ];
    }

    /**
     * Address without a VAT ID in the merchant country
     *
     * @return array
     */
    private function getFrenchAddressData(): array
    {
        return [
            'firstname' => 'John',
            'lastname' => 'Smith',
            'street' => 'Rue du Moulin 13',
            'city' => 'Toulouse',
            'postcode' => '31000',
            'country_id' => 'FR',
            'telephone' => '0123456789',
            'vat_id' => '',
        ];
    }

    /**
     * Customer group for valid intra-union VAT IDs without tax, and a 20% French rate for retail customers
     *
     * @return void
     */
    private function createTaxSetup(): void
    {
        $taxClass = $this->objectManager->get(TaxClassInterfaceFactory::class)->create();
        $taxClass->setClassName('EU business customer')
            ->setClassType(TaxClassManagementInterface::TYPE_CUSTOMER);
        $taxClassId = (int)$this->objectManager->get(TaxClassRepositoryInterface::class)->save($taxClass);

        $group = $this->objectManager->get(GroupInterfaceFactory::class)->create();
        $group->setCode('EU business')->setTaxClassId($taxClassId);
        $this->intraUnionGroupId = (int)$this->objectManager->get(GroupRepositoryInterface::class)
            ->save($group)
            ->getId();
        $this->objectManager->get(MutableScopeConfigInterface::class)->setValue(
            'customer/create_account/viv_intra_union_group',
            $this->intraUnionGroupId,
            ScopeInterface::SCOPE_STORE,
            'default'
        );

        $rate = $this->objectManager->get(TaxRateInterfaceFactory::class)->create();
        $rate->setCode('FR VAT')->setTaxCountryId('FR')->setTaxPostcode('*')->setRate(20);
        $rateId = (int)$this->objectManager->get(TaxRateRepositoryInterface::class)->save($rate)->getId();

        $rule = $this->objectManager->get(TaxRuleInterfaceFactory::class)->create();
        $rule->setCode('FR VAT for retail customers')
            ->setTaxRateIds([$rateId])
            ->setCustomerTaxClassIds([self::RETAIL_CUSTOMER_TAX_CLASS_ID])
            ->setProductTaxClassIds([self::TAXABLE_GOODS_TAX_CLASS_ID])
            ->setPriority(0)
            ->setPosition(0);
        $this->objectManager->get(TaxRuleRepositoryInterface::class)->save($rule);
    }

    /**
     * Answer every VAT ID check as valid instead of calling the VIES service
     *
     * @return void
     */
    private function mockVatValidation(): void
    {
        $vatMock = $this->getMockBuilder(Vat::class)
            ->setConstructorArgs(
                [
                    'scopeConfig' => $this->objectManager->get(ScopeConfigInterface::class),
                    'logger' => $this->objectManager->get(LoggerInterface::class),
                ]
            )
            ->onlyMethods(['checkVatNumber'])
            ->getMock();
        $vatMock->method('checkVatNumber')->willReturn(
            new DataObject(
                [
                    'is_valid' => true,
                    'request_date' => '2026-11-10',
                    'request_identifier' => 'test',
                    'request_success' => true,
                ]
            )
        );
        $this->objectManager->removeSharedInstance(CollectTotalsObserver::class);
        $this->objectManager->removeSharedInstance(VatValidator::class);
        $this->objectManager->removeSharedInstance(Vat::class);
        $this->objectManager->addSharedInstance($vatMock, Vat::class);
    }
}
