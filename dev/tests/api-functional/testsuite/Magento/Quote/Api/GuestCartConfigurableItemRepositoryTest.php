<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Quote\Api;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as AttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Test for Magento\Quote\Api\GuestCartConfigurableItemRepositoryTest.
 */
class GuestCartConfigurableItemRepositoryTest extends WebapiAbstract
{

    private const RESOURCE_PATH_GUEST_CART = '/V1/guest-carts/';

    private const SERVICE_VERSION_GUEST_CART = 'V1';

    private const SERVICE_NAME_GUEST_CART = 'quoteGuestCartManagementV1';

    private const SERVICE_NAME_GUEST_CART_ITEM = 'quoteGuestCartItemRepositoryV1';

    /**
     * @var array
     */
    private $simpleProductSkus = [];

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * Test guest cart update with configurable item
     *
     * @return void
     * @throws NoSuchEntityException
     */
    #[
        DataFixture(ProductFixture::class, ['price' => 10, 'sku' => 'simple-10'], as: 'p1'),
        DataFixture(ProductFixture::class, ['price' => 20, 'sku' => 'simple-20'], as: 'p2'),
        DataFixture(AttributeFixture::class, ['attribute_code' => 'test_configurable'], as: 'attr'),
        DataFixture(
            ConfigurableProductFixture::class,
            [
                'sku' => 'configurable',
                'name' => 'Configurable Product',
                '_options' => ['$attr$'],
                '_links' => ['$p1$', '$p2$']
            ],
            'configurableProduct'
        )
    ]
    public function testGuestCartUpdateConfigurableItem(): void
    {
        $guestCartId = $this->createGuestCart();
        $response = $this->addConfigurableProductToCart($guestCartId);
        $this->updateConfigurableProductInCart($guestCartId, $response['item_id']);
        $this->verifyCartItems($guestCartId, $response['item_id']);
    }

    /**
     * Create a guest cart and return its ID.
     *
     * @return string
     */
    private function createGuestCart(): string
    {
        $quoteId = $this->_webApiCall([
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH_GUEST_CART,
                'httpMethod' => Request::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => self::SERVICE_NAME_GUEST_CART,
                'serviceVersion' => self::SERVICE_VERSION_GUEST_CART,
                'operation' => self::SERVICE_NAME_GUEST_CART . 'CreateEmptyCart',
            ],
        ], ['storeId' => 1]);
        $this->assertTrue(strlen($quoteId) >= 32);

        return $quoteId;
    }

    /**
     * Add a configurable product to the guest cart.
     *
     * @param string $guestCartId
     * @return array
     * @throws NoSuchEntityException
     */
    private function addConfigurableProductToCart(string $guestCartId): array
    {
        $configurableProduct = $this->getConfigurableProduct();
        $optionData = $this->getConfigurableOptionData($configurableProduct);
        $requestData = $this->buildCartItemRequestData(
            $guestCartId,
            $configurableProduct->getSku(),
            $optionData['attribute_id'],
            $optionData['option_id']
        );
        $serviceInfo = $this->getCartServiceInfo($guestCartId, 'add');
        $response = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertNotNull($response['item_id']);
        $this->assertEquals(Configurable::TYPE_CODE, $response['product_type']);
        $this->assertEquals($requestData['cartItem']['qty'], $response['qty']);
        $this->assertContains($response['sku'], $this->simpleProductSkus);
        $this->assertEquals(
            $response['product_option']['extension_attributes']['configurable_item_options'][0],
            $requestData['cartItem']['product_option']['extension_attributes']['configurable_item_options'][0]
        );

        return $response;
    }

    /**
     * Verify that the cart contains the expected items.
     *
     * @param string $guestCartId
     * @param int $expectedItemId
     * @return void
     */
    private function verifyCartItems(string $guestCartId, int $expectedItemId): void
    {
        $serviceInfo = $this->getCartServiceInfo($guestCartId, 'get');
        $response = $this->_webApiCall($serviceInfo, ['cartId' => $guestCartId]);
        $this->assertIsArray($response);
        $this->assertGreaterThan(0, count($response), 'Cart should contain at least one item');

        $foundItem = false;
        foreach ($response as $item) {
            if ($item['item_id'] == $expectedItemId) {
                $foundItem = true;
                $this->assertEquals(Configurable::TYPE_CODE, $item['product_type']);
                $this->assertEquals(1, $item['qty']);
                $this->assertArrayHasKey('product_option', $item);
                $this->assertContains($item['sku'], $this->simpleProductSkus);
                $this->assertArrayHasKey('extension_attributes', $item['product_option']);
                $this->assertArrayHasKey(
                    'configurable_item_options',
                    $item['product_option']['extension_attributes']
                );
                break;
            }
        }

        $this->assertTrue($foundItem, 'Expected cart item not found in cart items list');
    }

    /**
     * Update a configurable product in the guest cart.
     *
     * @param string $guestCartId
     * @param int $itemId
     * @return void
     * @throws NoSuchEntityException
     */
    private function updateConfigurableProductInCart(string $guestCartId, int $itemId): void
    {
        $configurableProduct = $this->getConfigurableProduct();
        $optionData = $this->getConfigurableOptionData($configurableProduct);
        $requestData = $this->buildCartItemRequestData(
            $guestCartId,
            $configurableProduct->getSku(),
            $optionData['attribute_id'],
            $optionData['option_id']
        );
        $requestData['cartItem']['item_id'] = $itemId;
        $serviceInfo = $this->getCartServiceInfo($guestCartId, 'update', $itemId);
        $response = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertNotNull($response['item_id']);
        $this->assertEquals(Configurable::TYPE_CODE, $response['product_type']);
        $this->assertEquals($requestData['cartItem']['qty'], $response['qty']);
        $this->assertContains($response['sku'], $this->simpleProductSkus);
        $this->assertEquals(
            $response['product_option']['extension_attributes']['configurable_item_options'][0],
            $requestData['cartItem']['product_option']['extension_attributes']['configurable_item_options'][0]
        );
    }

    /**
     * Get configurable product from fixtures
     *
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    private function getConfigurableProduct(): ProductInterface
    {
        $configurableProduct = $this->fixtures->get('configurableProduct');
        $simpleProducts = $configurableProduct->getTypeInstance()->getUsedProducts($configurableProduct);
        foreach ($simpleProducts as $simpleProduct) {
            $this->simpleProductSkus[] = $simpleProduct->getSku();
        }

        return $configurableProduct;
    }

    /**
     * Get configurable option data for a configurable product.
     *
     * @param ProductInterface $configurableProduct
     * @return array
     */
    private function getConfigurableOptionData(ProductInterface $configurableProduct): array
    {
        $configOptions = $configurableProduct->getExtensionAttributes()->getConfigurableProductOptions();
        $options = $configOptions[0]->getOptions();
        $optionKey = isset($options[null]) ? null : 0;

        return [
            'attribute_id' => $configOptions[0]->getAttributeId(),
            'option_id' => $options[$optionKey]['value_index']
        ];
    }

    /**
     * Build the request data for adding or updating a cart item.
     *
     * @param string $cartId
     * @param string $sku
     * @param string $attributeId
     * @param string $optionId
     * @return array[]
     */
    private function buildCartItemRequestData(
        string $cartId,
        string $sku,
        string $attributeId,
        string $optionId
    ): array {
        return [
            'cartItem' => [
                'sku' => $sku,
                'qty' => 1,
                'quote_id' => $cartId,
                'product_option' => [
                    'extension_attributes' => [
                        'configurable_item_options' => [
                            [
                                'option_id' => $attributeId,
                                'option_value' => $optionId,
                            ]
                        ]
                    ]
                ]
            ],
        ];
    }

    /**
     * Get the service information for the cart operations.
     *
     * @param string $cartId
     * @param string $action
     * @param int|null $itemId
     * @return array[]
     */
    private function getCartServiceInfo(
        string $cartId,
        string $action = 'add',
        ?int $itemId = null
    ): array {
        $resourcePath = self::RESOURCE_PATH_GUEST_CART . $cartId . '/items';
        if ($action === 'update' && $itemId !== null) {
            $resourcePath .= '/' . $itemId;
        }
        $httpMethod = Request::HTTP_METHOD_POST;
        if ($action === 'update') {
            $httpMethod = Request::HTTP_METHOD_PUT;
        } elseif ($action === 'get') {
            $httpMethod = Request::HTTP_METHOD_GET;
        }
        $soapOperation = match ($action) {
            'get' => self::SERVICE_NAME_GUEST_CART_ITEM . 'GetList',
            'add', 'update' => self::SERVICE_NAME_GUEST_CART_ITEM . 'Save',
            default => self::SERVICE_NAME_GUEST_CART_ITEM . 'Save'
        };

        return [
            'rest' => [
                'resourcePath' => $resourcePath,
                'httpMethod' => $httpMethod
            ],
            'soap' => [
                'service' => self::SERVICE_NAME_GUEST_CART_ITEM,
                'serviceVersion' => self::SERVICE_VERSION_GUEST_CART,
                'operation' => $soapOperation,
            ],
        ];
    }
}
