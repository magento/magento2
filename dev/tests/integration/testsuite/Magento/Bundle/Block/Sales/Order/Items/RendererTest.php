<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Block\Sales\Order\Items;

use Magento\Bundle\Test\Fixture\Option as BundleOptionFixture;
use Magento\Bundle\Test\Fixture\OrderItem as OrderItemFixture;
use Magento\Bundle\Test\Fixture\Product as BundleProductFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Config\Model\ResourceModel\Config as CoreConfig;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Address as OrderAddress;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RendererTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;
    /**
     * @var Session
     */
    protected $session;

    /** @var Renderer */
    private $block;

    /**
     * @var CoreConfig
     */
    protected $resourceConfig;

    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var OrderSender
     */
    private $orderSender;

    /**
     * @defaultDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $layout = $this->objectManager->get(LayoutInterface::class);
        $this->block = $layout->createBlock(Renderer::class);
        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
        $this->resourceConfig = $this->objectManager->get(CoreConfig::class);
        $this->accountManagement = $this->objectManager->get(AccountManagementInterface::class);
        $this->orderSender = $this->objectManager->get(OrderSender::class);
    }

    #[
        DbIsolation(false),
        Config('default/currency/options/base', 'USD', 'store', 'default'),
        Config('currency/options/default', 'EUR', 'store', 'default'),
        Config('currency/options/allow', 'USD, EUR', 'store', 'default'),
        DataFixture(ProductFixture::class, ['price' => 10], 'p1'),
        DataFixture(ProductFixture::class, ['price' => 20], 'p2'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$p1$', '$p2$']], 'opt1'),
        DataFixture(BundleProductFixture::class, ['_options' => ['$opt1$']], 'bundle1'),
        DataFixture(OrderItemFixture::class, ['items' => [['sku' => '$bundle1.sku$']]], 'order'),
    ]
    public function testOrderEmailContent(): void
    {
        $order = $this->objectManager->create(Order::class);

        $incrementId =  $this->fixtures->get('order')->getIncrementId();
        $order->loadByIncrementId($incrementId);

        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $currencyCode = $storeManager->getWebsite()->getDefaultStore()->getDefaultCurrency()->getCode();
        $storeId = $this->objectManager->get(StoreManagerInterface::class)->getStore()->getId();
        $order->setStoreId($storeId);
        $order->setOrderCurrencyCode($currencyCode);
        $order->save();

        $priceBlockHtml = [];

        $items = $order->getAllItems();
        foreach ($items as $item) {
            $item->setProductOptions([
                'bundle_options' => [
                    [
                        'value' => [
                            ['title' => '']
                        ],
                    ],
                ],
                'bundle_selection_attributes' => '{"qty":5 ,"price":99}'
            ]);
            $this->block->setItem($item);
            $priceBlockHtml[] = $this->block->getValueHtml($item);
        }

        $this->assertStringContainsString("€99", $priceBlockHtml[0]);
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    #[
        DbIsolation(true),
        DataFixture(ProductFixture::class, ['price' => 10], 'p1'),
        DataFixture(ProductFixture::class, ['price' => 20], 'p2'),
        DataFixture(ProductFixture::class, ['price' => 30], 'p3'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$p1$', '$p2$', '$p3$']], 'opt1'),
        DataFixture(BundleProductFixture::class, ['_options' => ['$opt1$']], 'bundle1'),
        DataFixture(OrderItemFixture::class, ['items' => [['sku' => '$bundle1.sku$']]], 'order'),
        DataFixture(Customer::class, ['email' => 'customer@example.com'], as: 'customer'),
    ]
    public function testPlaceOrderWithOtherThanDefaultCurrencyValidateEmailHasSameCurrency(): void
    {
        $this->resourceConfig->saveConfig(
            'currency/options/default',
            'EUR',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );

        $this->resourceConfig->saveConfig(
            'currency/options/allow',
            'EUR',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );

        $this->resourceConfig->saveConfig(
            'currency/options/base',
            'USD',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );

        // Load customer data
        $customer = $this->fixtures->get('customer');
        $customerEmail = $customer->getEmail();

        // Login to customer
        $this->accountManagement->authenticate($customerEmail, 'password');

        // Including address data file
        $addressData = include __DIR__ . '/../../../../../Sales/_files/address_data.php';

        // Setting the billing address
        $billingAddress = $this->objectManager->create(OrderAddress::class, ['data' => $addressData]);
        $billingAddress->setAddressType('billing');

        // Setting the shipping address
        $shippingAddress = clone $billingAddress;
        $shippingAddress->setId(null)->setAddressType('shipping');

        // Place the order
        $order = $this->objectManager->create(Order::class);
        $incrementId = $this->fixtures->get('order')->getIncrementId();
        $order->loadByIncrementId($incrementId);
        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $currencyCodeSymbol = $storeManager->getWebsite()->getDefaultStore()->getDefaultCurrency()->getCurrencySymbol();
        $storeId = $this->objectManager->get(StoreManagerInterface::class)->getStore()->getId();
        $order->setStoreId($storeId);
        $order->setCustomerEmail($customerEmail);
        $order->setBillingAddress($billingAddress);
        $order->setShippingAddress($shippingAddress);
        $order->save();
        $this->orderSender->send($order);
        $this->assertTrue($order->getSendEmail());

        /** @var TransportBuilderMock $transportBuilderMock */
        $transportBuilderMock = Bootstrap::getObjectManager()
            ->get(TransportBuilderMock::class);
        $sentMessage = $transportBuilderMock->getSentMessage();

        $this->assertNotNull($sentMessage);
        $this->assertStringContainsString($currencyCodeSymbol, $sentMessage->getBodyText());
    }
}
