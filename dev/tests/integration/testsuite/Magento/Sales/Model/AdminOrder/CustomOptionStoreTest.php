<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\AdminOrder;

use Magento\Backend\Model\Session\Quote as AdminQuoteSession;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Option as OptionResource;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureBeforeTransaction;
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
class CustomOptionStoreTest extends TestCase
{
    /**
     * Reloaded admin quotes must retain the selected store's custom option titles.
     */
    #[DataProvider('optionTitlesProvider')]
    #[Config('payment/checkmo/active', '1')]
    #[DataFixtureBeforeTransaction(StoreFixture::class, as: 'store')]
    #[DataFixture(CustomerFixture::class, as: 'customer')]
    #[DataFixture(
        ProductFixture::class,
        [
            'type_id' => 'virtual',
            'options' => [
                [
                    'type' => 'drop_down',
                    'title' => 'Default option',
                    'values' => [['title' => 'Default value', 'price' => 0]],
                ],
            ],
        ],
        as: 'product'
    )]
    public function testOrderOptionTitles(bool $translate, string $expectedLabel, string $expectedValue): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $fixtures = DataFixtureStorageManager::getStorage();
        $storeId = (int)$fixtures->get('store')->getId();
        $customer = $fixtures->get('customer');
        $storeManager = $objectManager->get(StoreManagerInterface::class);
        $currentStoreId = (int)$storeManager->getDefaultStoreView()->getId();
        $productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $sku = $fixtures->get('product')->getSku();
        $currentStoreProduct = $productRepository->get($sku, false, $currentStoreId, true);
        $this->saveOptionTitles($currentStoreProduct, 'Other option', 'Other value');
        $product = $productRepository->get($sku, false, $storeId, true);
        if ($translate) {
            $this->saveOptionTitles($product, 'Store option', 'Store value');
        }
        $option = array_values($product->getOptions())[0];
        $value = array_values($option->getValues())[0];

        $session = $objectManager->get(AdminQuoteSession::class);
        $session->clearStorage();
        $session->setStoreId($storeId);
        $session->setCustomerId($customer->getId());
        $create = $objectManager->create(Create::class);
        $create->addProduct((int)$product->getId(), [
            'qty' => 1,
            'options' => [$option->getOptionId() => $value->getOptionTypeId()],
        ]);
        $quote = $create->getQuote();
        $objectManager->get(CartRepositoryInterface::class)->save($quote);

        $storeManager->setCurrentStore($currentStoreId);
        self::assertNotEquals($storeId, $storeManager->getStore()->getId());
        $reloadedQuote = $objectManager->get(QuoteFactory::class)->create();
        $objectManager->get(QuoteResource::class)->load($reloadedQuote, $quote->getId());
        $reloadedQuote->setIsSuperMode(true);
        $create->setQuote($reloadedQuote);
        $reloadedQuote->getBillingAddress()->addData([
            'firstname' => $customer->getFirstname(),
            'lastname' => $customer->getLastname(),
            'email' => $customer->getEmail(),
            'street' => ['123 Test Street'],
            'city' => 'Boston',
            'region_id' => 32,
            'postcode' => '02108',
            'country_id' => 'US',
            'telephone' => '1234567890',
        ]);
        $reloadedQuote->getPayment()->setMethod('checkmo');
        $reloadedQuote->collectTotals();
        $objectManager->get(CartRepositoryInterface::class)->save($reloadedQuote);
        $item = $reloadedQuote->getAllVisibleItems()[0];
        $options = $item->getProduct()->getTypeInstance()->getOrderOptions($item->getProduct());
        self::assertSame($expectedLabel, $options['options'][0]['label']);
        self::assertSame($expectedValue, $options['options'][0]['value']);

        $order = $create->createOrder();
        $order = $objectManager->get(OrderRepositoryInterface::class)->get($order->getEntityId());
        self::assertSame($storeId, (int)$order->getStoreId());
        $orderItem = array_values($order->getItems())[0];
        $options = $orderItem->getProductOptions()['options'];
        self::assertSame($expectedLabel, $options[0]['label']);
        self::assertSame($expectedValue, $options[0]['value']);
        self::assertSame($expectedValue, $options[0]['print_value']);
    }

    /**
     * @return array
     */
    public static function optionTitlesProvider(): array
    {
        return [
            'store titles' => [true, 'Store option', 'Store value'],
            'default titles' => [false, 'Default option', 'Default value'],
        ];
    }

    /**
     * Set store-view overrides without changing the default titles.
     */
    private function saveOptionTitles(Product $product, string $label, string $title): void
    {
        $option = array_values($product->getOptions())[0];
        $option->setStoreId((int)$product->getStoreId());
        $option->setTitle($label);
        $option->setIsUseDefault(false);
        $value = array_values($option->getValues())[0];
        $value->setTitle($title);
        $value->setIsUseDefault(false);
        Bootstrap::getObjectManager()->get(OptionResource::class)->save($option);
    }
}
