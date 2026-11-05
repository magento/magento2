<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\AdminOrder;

use Magento\Backend\Model\Session\Quote as AdminQuoteSession;
use Magento\Catalog\Test\Fixture\Virtual as ProductFixture;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Metadata\AttributeMetadataCache;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Customer\Model\ResourceModel\Attribute as AttributeResource;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[AppArea('adminhtml')]
#[AppIsolation(true)]
#[DbIsolation(true)]
class CreateTaxvatTest extends TestCase
{
    /**
     * Visibility before the test changes the taxvat attribute.
     *
     * @var bool|null
     */
    private ?bool $originalTaxvatVisibility = null;

    /**
     * Restore visibility and discard metadata cached during this test.
     */
    protected function tearDown(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        try {
            if ($this->originalTaxvatVisibility !== null) {
                $attribute = $objectManager->get(EavConfig::class)
                    ->getAttribute('customer', CustomerInterface::TAXVAT);
                $attribute->setIsVisible($this->originalTaxvatVisibility);
                $objectManager->get(AttributeResource::class)->save($attribute);
            }
        } finally {
            $objectManager->get(AttributeMetadataCache::class)->clean();
            $objectManager->get(EavConfig::class)->clear();
            parent::tearDown();
        }
    }

    /**
     * Verify account taxvat survives admin order submission and database persistence.
     */
    #[Config('payment/checkmo/active', '1')]
    #[DataFixture(CustomerFixture::class, [CustomerInterface::TAXVAT => 'original'], 'customer')]
    #[DataFixture(ProductFixture::class, as: 'product')]
    #[DataProvider('taxvatDataProvider')]
    public function testCreateOrder(
        bool $newCustomer,
        bool $visible,
        array $accountData,
        ?string $expected
    ): void {
        $objectManager = Bootstrap::getObjectManager();
        $fixtures = DataFixtureStorageManager::getStorage();
        $customer = $fixtures->get('customer');
        $product = $fixtures->get('product');
        $attribute = $objectManager->get(EavConfig::class)->getAttribute('customer', CustomerInterface::TAXVAT);
        $this->originalTaxvatVisibility = (bool)$attribute->getIsVisible();
        $attribute->setIsVisible($visible);
        $objectManager->get(AttributeResource::class)->save($attribute);
        self::assertSame(
            $visible,
            (bool)$objectManager->get(CustomerMetadataInterface::class)
                ->getAttributeMetadata(CustomerInterface::TAXVAT)->isVisible()
        );
        $session = $objectManager->get(AdminQuoteSession::class);
        $session->clearStorage();
        $session->setStoreId($objectManager->get(StoreManagerInterface::class)->getStore('default')->getId());
        $session->setCustomerId($newCustomer ? 0 : $customer->getId());
        $create = $objectManager->create(Create::class);
        $quote = $create->getQuote();
        if (!$newCustomer) {
            self::assertSame('original', $quote->getCustomerTaxvat());
        }
        $create->addProduct($product, ['qty' => 1]);
        $create->saveQuote();
        // Reload as the next admin request would: addresses cache their item list while the quote is still empty.
        $quote = $objectManager->create(Quote::class);
        $objectManager->get(QuoteResource::class)->load($quote, $create->getQuote()->getId());
        $create->setQuote($quote);
        $create->setPaymentData(['method' => 'checkmo']);
        $create->setIsValidate(true)->importPostData([
            'account' => array_merge([
                'email' => $newCustomer ? 'new-taxvat@example.com' : $customer->getEmail(),
                'group_id' => 1,
            ], $accountData),
            'billing_address' => [
                'firstname' => 'First',
                'lastname' => 'Last',
                'street' => ['123 Main Street'],
                'city' => 'Boston',
                'country_id' => 'US',
                'region_id' => 32,
                'postcode' => '02108',
                'telephone' => '1234567890',
                'save_in_address_book' => 0,
            ],
        ]);
        self::assertSame($expected, $quote->getCustomerTaxvat());
        $order = $create->createOrder();
        self::assertSame($expected, $order->getCustomerTaxvat());
        // A cleared value is persisted as NULL.
        $persistedQuote = $objectManager->create(Quote::class);
        $objectManager->get(QuoteResource::class)->load($persistedQuote, $quote->getId());
        self::assertSame($expected ?: null, $persistedQuote->getCustomerTaxvat());
        $persistedOrder = $objectManager->create(OrderRepositoryInterface::class)->get($order->getId());
        self::assertSame($expected ?: null, $persistedOrder->getCustomerTaxvat());
        self::assertNotEmpty($order->getCustomerId());
    }

    /**
     * Customer types, field visibility, account requests, and expected taxvat.
     */
    public static function taxvatDataProvider(): array
    {
        return [
            'existing hidden' => [false, false, [], 'original'],
            'existing visible omitted' => [false, true, [], 'original'],
            'existing visible edited' => [false, true, [CustomerInterface::TAXVAT => 'updated'], 'updated'],
            'existing visible cleared' => [false, true, [CustomerInterface::TAXVAT => ''], ''],
            'new hidden' => [true, false, [], null],
            'new visible' => [true, true, [CustomerInterface::TAXVAT => 'new-taxvat'], 'new-taxvat'],
        ];
    }
}
