<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Service\V1;

use Magento\Catalog\Test\Fixture\Attribute;
use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\ConfigurableProduct\Test\Fixture\AddProductToCart as AddConfigurableProductToCartFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Framework\DataObject;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Indexer\Test\Fixture\Indexer;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;

class OrderUpdateV1Test extends WebapiAbstract
{
    private const RESOURCE_PATH = '/V1/orders';

    private const SERVICE_NAME = 'salesOrderRepositoryV1';

    private const SERVICE_VERSION = 'V1';

    /**
     * @var DataFixtureStorage
     */
    private DataFixtureStorage $fixture;

    /**
     * @var OrderItemRepositoryInterface
     */
    private OrderItemRepositoryInterface $orderItemRepository;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->fixture = DataFixtureStorageManager::getStorage();
        $this->orderItemRepository = ObjectManager::getInstance()->get(OrderItemRepositoryInterface::class);
    }

    #[
        AppArea('adminhtml'),
        DataFixture(
            Attribute::class,
            [
                'frontend_input' => 'select',
                'backend_type' => 'int',
                'options' => [
                    ['label' => 'option1', 'sort_order' => 0],
                    ['label' => 'option2', 'sort_order' => 1]
                ]
            ],
            as: 'attr'
        ),
        DataFixture(CategoryFixture::class, ['name' => 'Category'], 'category'),
        DataFixture(ProductFixture::class, ['price' => 200, 'category_ids' => ['$category.id$']], as: 'simple1'),
        DataFixture(ProductFixture::class, ['price' => 100, 'category_ids' => ['$category.id$']], as: 'simple2'),
        DataFixture(
            ConfigurableProductFixture::class,
            [
                '_options' => ['$attr$'], '_links' => ['$simple2$']
            ],
            as: 'cp1'
        ),
        DataFixture(Indexer::class, as: 'indexer'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$simple1.id$']),
        DataFixture(
            AddConfigurableProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$cp1.id$', 'child_product_id' => '$simple2.id$', 'qty' => 1]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order')
    ]
    public function testOrderUpdate()
    {
        $order = $this->fixture->get('order');
        $productOptions = [];
        foreach ($order->getItems() as $item) {
            if ($item->getProductType() === 'simple') {
                $productOptions[] = $item->getProductOptions();
            }
        }

        $getResult = $this->makeGetServiceCall($order);
        $this->makePostServiceCall($getResult);

        $resavedProductOptions = [];
        foreach ($order->getItems() as $item) {
            if ($item->getProductType() === 'simple') {
                $item = $this->orderItemRepository->get($item->getItemId());
                $resavedProductOptions[] = $item->getProductOptions();
            }
        }

        $this->assertEquals(
            json_encode($productOptions),
            json_encode($resavedProductOptions),
            'Product Options do not match.'
        );
    }

    /**
     * Makes GET service call.
     *
     * @param DataObject $order
     * @return array
     */
    private function makeGetServiceCall(DataObject $order): array
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $order->getId(),
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'get',
            ],
        ];
        return $this->_webApiCall($serviceInfo, ['id' => $order->getId()]);
    }

    /**
     * Makes POST service call.
     *
     * @param $orderData
     * @return array
     */
    private function makePostServiceCall($orderData): array
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'save',
            ],
        ];
        return $this->_webApiCall($serviceInfo, ['entity' => $orderData]);
    }
}
