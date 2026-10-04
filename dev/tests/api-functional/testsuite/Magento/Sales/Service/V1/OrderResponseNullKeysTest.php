<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Sales\Service\V1;

use Magento\Authorization\Test\Fixture\Role;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Api\AdminTokenServiceInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap as BootstrapHelper;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\User\Test\Fixture\User;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Sql\Expression;

class OrderResponseNullKeysTest extends WebapiAbstract
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var AdminTokenServiceInterface
     */
    private $adminToken;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->_markTestAsRestOnly();
        $this->fixtures = BootstrapHelper::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
        $this->adminToken = BootstrapHelper::getObjectManager()->get(AdminTokenServiceInterface::class);
    }

    #[
        DataFixture(Role::class, as: 'allRole'),
        DataFixture(User::class, ['role_id' => '$allRole.id$'], as: 'allUser'),
        ConfigFixture('cataloginventory/item_options/auto_return', 0),
        ConfigFixture('payment/checkmo/active', '1'),
        ConfigFixture('carriers/flatrate/active', '1'),
        DataFixture(ProductFixture::class, [
            'price' => 10.00,
            'quantity_and_stock_status' => ['qty' => 100, 'is_in_stock' => true]
        ], as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(SetGuestEmailFixture::class, [
            'cart_id' => '$cart.id$',
            'email' => 'guest@example.com'
        ]),
        DataFixture(AddProductToCartFixture::class, [
            'cart_id' => '$cart.id$',
            'product_id' => '$product.id$',
            'qty' => 1
        ]),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, [
            'cart_id' => '$cart.id$',
            'carrier_code' => 'flatrate',
            'method_code' => 'flatrate'
        ]),
        DataFixture(SetPaymentMethodFixture::class, [
            'cart_id' => '$cart.id$',
            'method' => 'checkmo'
        ]),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], as: 'order'),
    ]
    public function testUserWithRestrictedWebsiteAndStoreGroup()
    {
        $order = $this->fixtures->get('order');
        $orderId = (int) $order->getId();
        $this->nullifyOrderStateStatus($orderId);

        $user = $this->fixtures->get('allUser');
        $accessToken = $this->getAccessToken($user->getUsername());
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/orders/' . $orderId,
                'httpMethod' => 'GET',
                'token' => $accessToken
            ]
        ];
        $result = $this->_webApiCall($serviceInfo);

        $this->assertIsArray($result);
        $this->assertSame($orderId, (int)$result['entity_id']);
        $this->assertArrayHasKey('state', $result);
        $this->assertArrayHasKey('status', $result);
        $this->assertNull($result['state']);
        $this->assertNull($result['status']);
    }

    /**
     * Update order status and state field as null
     *
     * @param int $orderId
     * @return void
     */
    private function nullifyOrderStateStatus(int $orderId): void
    {
        $om = Bootstrap::getObjectManager();
        $resource = $om->get(ResourceConnection::class);
        $connection = $resource->getConnection();
        $table = $resource->getTableName('sales_order');
        $connection->update(
            $table,
            ['state' => new Expression('NULL'), 'status' => new Expression('NULL')],
            ['entity_id = ?' => $orderId]
        );
    }

    /**
     * Get admin access token
     *
     * @param string $username
     * @param string $password
     * @return string
     * @throws AuthenticationException
     * @throws InputException
     * @throws LocalizedException
     */
    private function getAccessToken(string $username, string $password = 'password1'): string
    {
        return $this->adminToken->createAdminAccessToken($username, $password);
    }
}
