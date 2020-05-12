<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test cases for adding downloadable product with custom options to cart.
 */
class AddDownloadableProductWithCustomOptionsToCartTest extends GraphQlAbstract
{
    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var GetCustomOptionsValuesForQueryBySku
     */
    private $getCustomOptionsValuesForQueryBySku;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $this->objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->getCustomOptionsValuesForQueryBySku =
            $this->objectManager->get(GetCustomOptionsValuesForQueryBySku::class);
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable_with_custom_options.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddDownloadableProductWithOptions()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');

        $sku = 'downloadable-product-with-purchased-separately-links';
        $qty = 1;
        $links = $this->getProductsLinks($sku);
        $linkId = key($links);

        $customOptionsValues = $this->getCustomOptionsValuesForQueryBySku->execute($sku);
        /* Generate customizable options fragment for GraphQl request */
        $queryCustomizableOptionValues = preg_replace(
            '/"([^"]+)"\s*:\s*/',
            '$1:',
            json_encode(array_values($customOptionsValues))
        );
        $customizableOptions = "customizable_options: {$queryCustomizableOptionValues}";

        $query = $this->getQuery($maskedQuoteId, $qty, $sku, $customizableOptions, $linkId);

        $response = $this->graphQlMutation($query);
        self::assertArrayHasKey('items', $response['addDownloadableProductsToCart']['cart']);
        self::assertCount($qty, $response['addDownloadableProductsToCart']['cart']);
        $customizableOptionsOutput =
            $response['addDownloadableProductsToCart']['cart']['items'][0]['customizable_options'];
        $count = 0;
        foreach ($customOptionsValues as $value) {
            $expectedValues = $this->buildExpectedValuesArray($value['value_string']);
            self::assertEquals(
                $expectedValues,
                $customizableOptionsOutput[$count]['values']
            );
            $count++;
        }
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable_with_custom_options.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddDownloadableProductWithMissedRequiredOptionsSet()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');

        $sku = 'downloadable-product-with-purchased-separately-links';
        $qty = 1;
        $links = $this->getProductsLinks($sku);
        $linkId = key($links);
        $customizableOptions = '';

        $query = $this->getQuery($maskedQuoteId, $qty, $sku, $customizableOptions, $linkId);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'The product\'s required option(s) weren\'t entered. Make sure the options are entered and try again.'
        );

        $this->graphQlMutation($query);
    }

    /**
     * Function returns array of all product's links
     *
     * @param string $sku
     * @return array
     */
    private function getProductsLinks(string $sku) : array
    {
        $result = [];
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);

        $product = $productRepository->get($sku, false, null, true);

        foreach ($product->getDownloadableLinks() as $linkObject) {
            $result[$linkObject->getLinkId()] = [
                'title' => $linkObject->getTitle(),
                'link_type' => null, //deprecated field
                'price' => $linkObject->getPrice(),
            ];
        }

        return $result;
    }

    /**
     * Build the part of expected response.
     *
     * @param string $assignedValue
     * @return array
     */
    private function buildExpectedValuesArray(string $assignedValue) : array
    {
        $assignedOptionsArray = explode(',', trim($assignedValue, '[]'));
        $expectedArray = [];
        foreach ($assignedOptionsArray as $assignedOption) {
            $expectedArray[] = ['value' => $assignedOption];
        }
        return $expectedArray;
    }

    /**
     * Returns GraphQl query string
     *
     * @param string $maskedQuoteId
     * @param int $qty
     * @param string $sku
     * @param string $customizableOptions
     * @param $linkId
     * @return string
     */
    private function getQuery(
        string $maskedQuoteId,
        int $qty,
        string $sku,
        string $customizableOptions,
        $linkId
    ): string {
        $query = <<<MUTATION
mutation {
    addDownloadableProductsToCart(
        input: {
            cart_id: "{$maskedQuoteId}", 
            cart_items: [
                {
                    data: {
                        quantity: {$qty}, 
                        sku: "{$sku}"
                    },
                    {$customizableOptions}
                    downloadable_product_links: [
                        {
          	                link_id: {$linkId}
                        }
                    ]
                }
            ]
        }
    ) {
        cart {
            items {
                quantity
                ... on DownloadableCartItem {
                    customizable_options {
                        label
                          values {
                            value
                        }
                    }
                }
            }
        }
    }
}
MUTATION;
        return $query;
    }
}
