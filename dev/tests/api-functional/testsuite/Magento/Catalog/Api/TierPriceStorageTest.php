<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * TierPriceStorage test.
 */
class TierPriceStorageTest extends WebapiAbstract
{
    const SERVICE_NAME = 'catalogTierPriceStorageV1';
    const SERVICE_VERSION = 'V1';
    const SIMPLE_PRODUCT_SKU = 'simple';

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    /**
     * Set up.
     */
    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * Test get method.
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testGet()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/tier-prices-information',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];
        $response = $this->_webApiCall($serviceInfo, ['skus' => [self::SIMPLE_PRODUCT_SKU]]);
        $productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
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
     */
    public function testUpdate()
    {
        $productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $prices = $productRepository->get(self::SIMPLE_PRODUCT_SKU)->getTierPrices();
        $tierPrice = array_shift($prices);
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/tier-prices',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Update',
            ],
        ];
        $newPrice = [
            'price' => 40,
            'price_type' => \Magento\Catalog\Api\Data\TierPriceInterface::PRICE_TYPE_DISCOUNT,
            'website_id' => 0,
            'sku' => self::SIMPLE_PRODUCT_SKU,
            'customer_group' => 'ALL GROUPS',
            'quantity' => 7778
        ];
        $updatedPrice = [
            'price' => 778,
            'price_type' => \Magento\Catalog\Api\Data\TierPriceInterface::PRICE_TYPE_FIXED,
            'website_id' => 0,
            'sku' => self::SIMPLE_PRODUCT_SKU,
            'customer_group' => 'not logged in',
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
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Update',
            ],
        ];
        $invalidPrice = [
            'price' => 40,
            'price_type' => \Magento\Catalog\Api\Data\TierPriceInterface::PRICE_TYPE_FIXED,
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
        $message = 'We found a duplicate website, tier price, customer group and quantity: '
            . 'Customer Group = %customerGroup, Website ID = %websiteId, Quantity = %qty. '
            . 'Row ID: SKU = %SKU, Website ID: %websiteId, Customer Group: %customerGroup, Quantity: %qty.';
        $this->assertEquals($message, $response[1]['message']);
        $this->assertEquals('simple', $response[1]['parameters'][0]);
        $this->assertEquals('0', $response[1]['parameters'][1]);
        $this->assertEquals('NOT LOGGED IN', $response[1]['parameters'][2]);
        $this->assertEquals('3.0000', $response[1]['parameters'][3]);
    }

    /**
     * Test replace method.
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testReplace()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/tier-prices',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT
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
                'price_type' => \Magento\Catalog\Api\Data\TierPriceInterface::PRICE_TYPE_DISCOUNT,
                'website_id' => 0,
                'sku' => self::SIMPLE_PRODUCT_SKU,
                'customer_group' => 'general',
                'quantity' => 7778
            ],
            [
                'price' => 70,
                'price_type' => \Magento\Catalog\Api\Data\TierPriceInterface::PRICE_TYPE_FIXED,
                'website_id' => 0,
                'sku' => self::SIMPLE_PRODUCT_SKU,
                'customer_group' => 'not logged in',
                'quantity' => 33
            ]
        ];
        $response = $this->_webApiCall($serviceInfo, ['prices' => $newPrices]);
        $productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $tierPrices = $productRepository->get(self::SIMPLE_PRODUCT_SKU)->getTierPrices();
        $this->assertEmpty($response);
        $this->assertEquals(count($newPrices), count($tierPrices));
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
                ? \Magento\Catalog\Api\Data\TierPriceInterface::PRICE_TYPE_DISCOUNT
                : \Magento\Catalog\Api\Data\TierPriceInterface::PRICE_TYPE_FIXED;
            $customerGroup = $tierPrice->getCustomerGroupId() == \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID
                ? 'NOT LOGGED IN'
                : 'ALL GROUPS';
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
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST
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
        $this->assertEquals(1, count($tierPrices));
        $this->assertEquals($pricesToStore, $tierPrice);
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
            $priceIsCorrect = $price['price_type'] === \Magento\Catalog\Api\Data\TierPriceInterface::PRICE_TYPE_DISCOUNT
                ? (float)$tierPrice->getExtensionAttributes()->getPercentageValue() === (float)$price['price']
                : (float)$tierPrice->getValue() === (float)$price['price'];
            if (
                $priceIsCorrect
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
