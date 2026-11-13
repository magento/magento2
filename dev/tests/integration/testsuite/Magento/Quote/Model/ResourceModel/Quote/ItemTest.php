<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\ResourceModel\Quote;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\ConfigurableProduct\Test\Fixture\AddProductToCart as AddConfigurableProductToCartFixture;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as AttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Test\Fixture\CustomerCart as CustomerCartFixture;
use Magento\Sales\Model\Reorder\Reorder;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * @var DataFixtureStorage
     */
    private DataFixtureStorage $fixtures;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->objectManager->get(CustomerSession::class)->setCustomerId(null);
    }

    #[
        AppArea('frontend'),
        Config('cataloginventory/options/show_out_of_stock', '1', 'store', 'default'),
        DataFixture(ProductFixture::class, as: 'child1'),
        DataFixture(ProductFixture::class, as: 'child2'),
        DataFixture(AttributeFixture::class, as: 'attr'),
        DataFixture(
            ConfigurableProductFixture::class,
            ['_options' => ['$attr$'], '_links' => ['$child1$', '$child2$']],
            'configurable'
        ),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(
            AddConfigurableProductToCartFixture::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$configurable.id$',
                'child_product_id' => '$child1.id$',
            ]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
    ]
    public function testReorderOfDisabledConfigurableVariantSavesCart(): void
    {
        $orderedChild = $this->fixtures->get('child1');
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->getById($orderedChild->getId(), true, 0, true);
        $product->setStatus(Status::STATUS_DISABLED);
        $productRepository->save($product);

        $order = $this->fixtures->get('order');
        $this->objectManager->get(CustomerSession::class)->setCustomerId($order->getCustomerId());

        // Same argument as Magento/Sales/etc/frontend/di.xml, the storefront "Reorder" link configuration.
        $reorder = $this->objectManager->create(Reorder::class, ['addToCartInvalidProduct' => true]);
        $output = $reorder->execute($order->getIncrementId(), (string)$order->getStoreId());

        $this->assertNotNull($output->getCart()->getId());
        foreach ($output->getCart()->getAllItems() as $item) {
            $this->assertNotEquals($orderedChild->getId(), $item->getProductId());
        }
    }
}
