<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Api;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Test\Fixture\CustomerCart as CustomerCartFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Forced cartItem.quote_id substitution on POST /V1/carts/mine/items
 */
class CartItemMalformedPayloadTest extends WebapiAbstract
{
    private const RESOURCE_PATH = '/V1/carts/mine/items';

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    protected function setUp(): void
    {
        $this->_markTestAsRestOnly();
        parent::setUp();
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    #[
        DataProvider('malformedCartItemDataProvider'),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], 'cart'),
    ]
    public function testMalformedCartItemIsRejected(array $requestData, array $expectedParameters): void
    {
        $exception = null;
        try {
            $this->_webApiCall($this->getServiceInfo(), $requestData);
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->assertNotNull($exception, 'A malformed cartItem payload must be rejected.');
        $this->assertEquals(WebapiException::HTTP_BAD_REQUEST, $exception->getCode());
        $this->assertEquals(
            [
                'message' => 'Invalid value of "%value" provided for the %fieldName field.',
                'parameters' => $expectedParameters,
            ],
            $this->processRestExceptionResult($exception)
        );
    }

    public static function malformedCartItemDataProvider(): array
    {
        return [
            'string cartItem' => [
                ['cartItem' => 'invalid'],
                ['fieldName' => 'cartItem', 'value' => 'invalid'],
            ],
            'integer cartItem' => [
                ['cartItem' => 5],
                ['fieldName' => 'cartItem', 'value' => 5],
            ],
            'string cart_item' => [
                ['cart_item' => 'invalid'],
                ['fieldName' => 'cart_item', 'value' => 'invalid'],
            ],
        ];
    }

    #[
        DataProvider('cartItemKeyCaseDataProvider'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], 'cart'),
        DataFixture(CustomerFixture::class, as: 'other_customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$other_customer.id$'], 'other_cart'),
    ]
    public function testQuoteIdIsForcedToCustomerCart(string $quoteIdKey): void
    {
        $cartId = (int)$this->fixtures->get('cart')->getId();
        $otherCartId = (int)$this->fixtures->get('other_cart')->getId();
        $requestData = [
            'cartItem' => [
                'sku' => $this->fixtures->get('product')->getSku(),
                'qty' => 1,
                $quoteIdKey => $otherCartId,
            ],
        ];

        $result = $this->_webApiCall($this->getServiceInfo(), $requestData);

        $this->assertEquals($cartId, (int)$result['quote_id']);
    }

    public static function cartItemKeyCaseDataProvider(): array
    {
        return [
            'snake_case quote_id' => ['quote_id'],
            'camelCase quoteId' => ['quoteId'],
        ];
    }

    private function getServiceInfo(): array
    {
        $token = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class)
            ->createCustomerAccessToken($this->fixtures->get('customer')->getEmail(), 'password');

        return [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
                'token' => $token,
            ],
        ];
    }
}
