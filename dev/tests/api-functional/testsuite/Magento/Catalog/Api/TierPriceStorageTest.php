<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\TierPriceInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Customer\Model\Group;

/**
 * Test all API calls for tier price storage.
 */
class TierPriceStorageTest extends WebapiAbstract
{
    private const SERVICE_NAME = 'catalogTierPriceStorageV1';
    private const SERVICE_VERSION = 'V1';
    private const SIMPLE_PRODUCT_SKU = 'simple';
    private const CUSTOMER_ALL_GROUPS_NAME ='ALL GROUPS';
    private const CUSTOMER_GENERAL_GROUP_NAME ='General';
    private const CUSTOMER_NOT_LOGGED_IN_GROUP_NAME ='NOT LOGGED IN';
    private const WRONG_CUSTOMER_GROUP_NAME ='general';

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    /**
     * Set up.
     */
    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * Test get method.
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoConfigFixture default_store catalog/price/scope 0
     */
    public function testGet()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/tier-prices-information',
                'httpMethod' => Request::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];
        $response = $this->_webApiCall($serviceInfo, ['skus' => [self::SIMPLE_PRODUCT_SKU]]);
        $productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        /** @var ProductInterface $product */
        $tierPrices = $productRepository->get(self::SIMPLE_PRODUCT_SKU)->getTierPrices();
        $this->assertNotEmpty($response);
        $this->assertEquals(count($response), count($tierPrices));
        foreach ($response as $item) {
            $this->assertTrue($this->isPriceCorrect($item, $tierPrices));
        }
    }

    /**
     * Test update method.
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoConfigFixture default_store catalog/price/scope 0
     */
    public function testUpdate()
    {
        $productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $prices = $productRepository->get(self::SIMPLE_PRODUCT_SKU)->getTierPrices();
        $tierPrice = array_shift($prices);
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/tier-prices',
                'httpMethod' => Request::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Update',
            ],
        ];
        $newPrice = [
            'price' => 40,
            'price_type' => TierPriceInterface::PRICE_TYPE_DISCOUNT,
            'website_id' => 0,
            'sku' => self::SIMPLE_PRODUCT_SKU,
            'customer_group' => self::CUSTOMER_ALL_GROUPS_NAME,
            'quantity' => 7778
        ];
        $updatedPrice = [
            'price' => 778,
            'price_type' => TierPriceInterface::PRICE_TYPE_FIXED,
            'website_id' => 0,
            'sku' => self::SIMPLE_PRODUCT_SKU,
            'customer_group' => self::CUSTOMER_NOT_LOGGED_IN_GROUP_NAME,
            'quantity' => $tierPrice->getQty()
        ];
        $response = $this->_webApiCall($serviceInfo, ['prices' => [$updatedPrice, $newPrice]]);
        $productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $tierPrices = $productRepository->get(self::SIMPLE_PRODUCT_SKU)->getTierPrices();
        $this->assertEmpty($response);
        $this->assertTrue($this->isPriceCorrect($newPrice, $tierPrices));
        $this->assertTrue($this->isPriceCorrect($updatedPrice, $tierPrices));
    }

    /**
     * Call update method with specifying new website value for tier price with all websites value.
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testUpdateWebsiteForAllWebsites()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/tier-prices',
                'httpMethod' => Request::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Update',
            ],
        ];
        $invalidPrice = [
            'price' => 40,
            'price_type' => TierPriceInterface::PRICE_TYPE_FIXED,
            'website_id' => 2,
            'sku' => self::SIMPLE_PRODUCT_SKU,
            'customer_group' => 'not logged in',
            'quantity' => 3
        ];
        $response = $this->_webApiCall($serviceInfo, ['prices' => [$invalidPrice]]);
        $this->assertNotEmpty($response);
        $message = 'Invalid attribute Website ID = %websiteId. '
            . 'Row ID: SKU = %SKU, Website ID: %websiteId, '
            . 'Customer Group: %customerGroup, Quantity: %qty.';
        $this->assertEquals($message, $response[0]['message']);
        $this->assertEquals('simple', $response[0]['parameters'][0]);
        $this->assertEquals('2', $response[0]['parameters'][1]);
        if (array_key_exists(1, $response)) {
            $message = 'We found a duplicate website, tier price, customer group and quantity: '
                . 'Customer Group = %customerGroup, Website ID = %websiteId, Quantity = %qty. '
                . 'Row ID: SKU = %SKU, Website ID: %websiteId, Customer Group: %customerGroup, Quantity: %qty.';
            $this->assertEquals($message, $response[1]['message']);
            $this->assertEquals('simple', $response[1]['parameters'][0]);
            $this->assertEquals('0', $response[1]['parameters'][1]);
            $this->assertEquals('NOT LOGGED IN', $response[1]['parameters'][2]);
            $this->assertEquals('3.0000', $response[1]['parameters'][3]);
        }
    }

    /**
     * Test replace method without error message.
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testReplaceWithoutErrorMessage()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/tier-prices',
                'httpMethod' => Request::HTTP_METHOD_PUT
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Replace',
            ],
        ];
        $newPrices = [
            [
                'price' => 50,
                'price_type' => TierPriceInterface::PRICE_TYPE_DISCOUNT,
                'website_id' => 0,
                'sku' => self::SIMPLE_PRODUCT_SKU,
                'customer_group' => self::CUSTOMER_GENERAL_GROUP_NAME,
                'quantity' => 7778
            ],
            [
                'price' => 70,
                'price_type' => TierPriceInterface::PRICE_TYPE_FIXED,
                'website_id' => 0,
                'sku' => self::SIMPLE_PRODUCT_SKU,
                'customer_group' => self::CUSTOMER_NOT_LOGGED_IN_GROUP_NAME,
                'quantity' => 33
            ]
        ];
        $response = $this->_webApiCall($serviceInfo, ['prices' => $newPrices]);
        $productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        /** @var ProductInterface $product */
        $tierPrices = $productRepository->get(self::SIMPLE_PRODUCT_SKU)->getTierPrices();
        $this->assertEmpty($response);
        $this->assertEquals(count($newPrices), count($tierPrices));
    }

    /**
     * Test replace method.
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testReplaceWithErrorMessage()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/tier-prices',
                'httpMethod' => Request::HTTP_METHOD_PUT
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Replace',
            ],
        ];
        $newPrices = [
            [
                'price' => 10.31,
                'price_type' => TierPriceInterface::PRICE_TYPE_FIXED,
                'website_id' => 0,
                'sku' => self::SIMPLE_PRODUCT_SKU,
                'customer_group' => self::WRONG_CUSTOMER_GROUP_NAME,
                'quantity' => 2
            ],
            [
                'price' => 20.62,
                'price_type' => TierPriceInterface::PRICE_TYPE_FIXED,
                'website_id' => 0,
                'sku' => self::SIMPLE_PRODUCT_SKU,
                'customer_group' => self::WRONG_CUSTOMER_GROUP_NAME,
                'quantity' => 2
            ]
        ];
        $response = $this->_webApiCall($serviceInfo, ['prices' => $newPrices]);
        $this->assertNotEmpty($response);
        $message = 'We found a duplicate website, tier price, customer group and quantity: '
            . 'Customer Group = %customerGroup, Website ID = %websiteId, Quantity = %qty. '
            . 'Row ID: SKU = %SKU, Website ID: %websiteId, Customer Group: %customerGroup, Quantity: %qty.';
        $this->assertEquals($message, $response[0]['message']);
        $this->assertEquals('simple', $response[0]['parameters'][0]);
        $this->assertEquals('0', $response[0]['parameters'][1]);
        $this->assertEquals('general', $response[0]['parameters'][2]);
        $this->assertEquals('2', $response[0]['parameters'][3]);
    }

    /**
     * Test delete method.
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testDelete()
    {
        $productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $tierPrices = $productRepository->get(self::SIMPLE_PRODUCT_SKU)->getTierPrices();
        $pricesToStore = array_pop($tierPrices);
        $pricesToDelete = [];
        foreach ($tierPrices as $tierPrice) {
            $tierPriceValue = $tierPrice->getExtensionAttributes()->getPercentageValue()
                ?: $tierPrice->getValue();
            $priceType = $tierPrice->getExtensionAttributes()->getPercentageValue()
                ? TierPriceInterface::PRICE_TYPE_DISCOUNT
                : TierPriceInterface::PRICE_TYPE_FIXED;
            $customerGroup = $tierPrice->getCustomerGroupId() == \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID
                ? self::CUSTOMER_NOT_LOGGED_IN_GROUP_NAME
                : self::CUSTOMER_ALL_GROUPS_NAME;
            $pricesToDelete[] = [
                'price' => $tierPriceValue,
                'price_type' => $priceType,
                'website_id' => 0,
                'customer_group' => $customerGroup,
                'sku' => self::SIMPLE_PRODUCT_SKU,
                'quantity' => $tierPrice->getQty()

            ];
        }
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/tier-prices-delete',
                'httpMethod' => Request::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Delete',
            ],
        ];
        $response = $this->_webApiCall($serviceInfo, ['prices' => $pricesToDelete]);
        $productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $tierPrices = $productRepository->get(self::SIMPLE_PRODUCT_SKU)->getTierPrices();
        $tierPrice = $tierPrices[0];
        $this->assertEmpty($response);
        $this->assertCount(1, $tierPrices);
        $this->assertEquals($pricesToStore, $tierPrice);
    }

    /**
     * Test to validate the incorrect website id.
     */
    #[
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'simple',
                'website_id' => 0,
                'tier_prices' => [
                    [
                        'customer_group_id' => Group::NOT_LOGGED_IN_ID,
                        'qty' => 3.2,
                        'value' => 6
                    ]
                ]
            ]
        )
    ]
    public function testCheckWebsite()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/tier-prices',
                'httpMethod' => Request::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Update',
            ],
        ];
        $tierPriceWithInvalidWebsiteId = [
            'price' => 38.97,
            'price_type' => TierPriceInterface::PRICE_TYPE_FIXED,
            'website_id' => 1,
            'sku' => self::SIMPLE_PRODUCT_SKU,
            'customer_group' => 'ALL GROUPS',
            'quantity' => 3,
            'extension_attributes' => []
        ];
        $response = $this->_webApiCall($serviceInfo, ['prices' => [$tierPriceWithInvalidWebsiteId]]);
        if (is_array($response) && count($response) > 0) {
            $this->assertNotEmpty($response);
            // phpcs:disable Generic.Files.LineLength.TooLong
            $message = 'Invalid attribute Website ID = %websiteId. Row ID: SKU = %SKU, Website ID: %websiteId, Customer Group: %customerGroup, Quantity: %qty.';
            $this->assertEquals($message, $response[0]['message']);
            $this->assertEquals(['simple', '1', 'ALL GROUPS', '3'], $response[0]['parameters']);
        }
    }

    /**
     * Test replace method.
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testCheckNewRecords()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/tier-prices',
                'httpMethod' => Request::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Update',
            ],
        ];

        $newPrices = [
            [
                'price' => 10.31,
                'price_type' => TierPriceInterface::PRICE_TYPE_FIXED,
                'website_id' => 0,
                'sku' => self::SIMPLE_PRODUCT_SKU,
                'customer_group' => self::CUSTOMER_ALL_GROUPS_NAME,
                'quantity' => 2
            ],
            [
                'price' => 20.62,
                'price_type' => TierPriceInterface::PRICE_TYPE_FIXED,
                'website_id' => 1,
                'sku' => self::SIMPLE_PRODUCT_SKU,
                'customer_group' => self::CUSTOMER_ALL_GROUPS_NAME,
                'quantity' => 2
            ]
        ];

        $response = $this->_webApiCall($serviceInfo, ['prices' => $newPrices]);
        $this->assertNotEmpty($response);
        $message = 'We found a duplicate website, tier price, customer group and quantity: '
            . 'Customer Group = %customerGroup, Website ID = %websiteId, Quantity = %qty. '
            . 'Row ID: SKU = %SKU, Website ID: %websiteId, Customer Group: %customerGroup, Quantity: %qty.';
        $this->assertEquals($message, $response[0]['message']);
        $this->assertEquals('simple', $response[0]['parameters'][0]);
    }

    /**
     * Check prise exists and is correct.
     *
     * @param array $price
     * @param array $tierPrices
     * @return bool
     */
    private function isPriceCorrect(array $price, array $tierPrices)
    {
        $isCorrect = false;
        foreach ($tierPrices as $tierPrice) {
            $priceIsCorrect = $price['price_type'] === TierPriceInterface::PRICE_TYPE_DISCOUNT
                ? (float)$tierPrice->getExtensionAttributes()->getPercentageValue() === (float)$price['price']
                : (float)$tierPrice->getValue() === (float)$price['price'];
            if ($priceIsCorrect
                && (int)$tierPrice->getQty() === (int)$price['quantity']
                && $tierPrice->getExtensionAttributes()->getWebsiteId() == $price['website_id']
            ) {
                $isCorrect = true;
                break;
            }
        }
        return $isCorrect;
    }
}
