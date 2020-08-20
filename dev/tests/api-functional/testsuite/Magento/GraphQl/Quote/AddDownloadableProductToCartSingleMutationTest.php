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
 * Test cases for adding downloadable product with custom options to cart using the single add to cart mutation.
 */
class AddDownloadableProductToCartSingleMutationTest extends GraphQlAbstract
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
     * @var GetCustomOptionsWithUIDForQueryBySku
     */
    private $getCustomOptionsWithIDV2ForQueryBySku;

    /**
     * @var GetCartItemOptionsFromUID
     */
    private $getCartItemOptionsFromUID;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $this->objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->getCartItemOptionsFromUID = $this->objectManager->get(GetCartItemOptionsFromUID::class);
        $this->getCustomOptionsWithIDV2ForQueryBySku =
            $this->objectManager->get(GetCustomOptionsWithUIDForQueryBySku::class);
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

        $itemOptions = $this->getCustomOptionsWithIDV2ForQueryBySku->execute($sku);
        $decodedItemOptions = $this->getCartItemOptionsFromUID->execute($itemOptions);

        /* The type field is only required for assertions, it should not be present in query */
        foreach ($itemOptions['entered_options'] as &$enteredOption) {
            if (isset($enteredOption['type'])) {
                unset($enteredOption['type']);
            }
        }

        /* Add downloadable product link data to the "selected_options" */
        $itemOptions['selected_options'][] = $this->generateProductLinkSelectedOptions($linkId);

        $productOptionsQuery = preg_replace(
            '/"([^"]+)"\s*:\s*/',
            '$1:',
            json_encode($itemOptions)
        );

        $query = $this->getQuery($maskedQuoteId, $qty, $sku, trim($productOptionsQuery, '{}'));
        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('items', $response['addProductsToCart']['cart']);
        self::assertCount($qty, $response['addProductsToCart']['cart']);
        self::assertEquals($linkId, $response['addProductsToCart']['cart']['items'][0]['links'][0]['id']);

        $customizableOptionsOutput =
            $response['addProductsToCart']['cart']['items'][0]['customizable_options'];

        foreach ($customizableOptionsOutput as $customizableOptionOutput) {
            $customizableOptionOutputValues = [];
            foreach ($customizableOptionOutput['values'] as $customizableOptionOutputValue) {
                $customizableOptionOutputValues[] =  $customizableOptionOutputValue['value'];
            }
            if (count($customizableOptionOutputValues) === 1) {
                $customizableOptionOutputValues = $customizableOptionOutputValues[0];
            }

            self::assertEquals(
                $decodedItemOptions[$customizableOptionOutput['id']],
                $customizableOptionOutputValues
            );
        }
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
     * Generates UID for downloadable links
     *
     * @param int $linkId
     * @return string
     */
    private function generateProductLinkSelectedOptions(int $linkId): string
    {
        return base64_encode("downloadable/$linkId");
    }

    /**
     * Returns GraphQl query string
     *
     * @param string $maskedQuoteId
     * @param int $qty
     * @param string $sku
     * @param string $customizableOptions
     * @return string
     */
    private function getQuery(
        string $maskedQuoteId,
        int $qty,
        string $sku,
        string $customizableOptions
    ): string {
        return <<<MUTATION
mutation {
    addProductsToCart(
        cartId: "{$maskedQuoteId}",
        cartItems: [
            {
                sku: "{$sku}"
                quantity: {$qty}
                {$customizableOptions}
            }
        ]
    ) {
        cart {
            items {
                quantity
                ... on DownloadableCartItem {
                    links {
                        id
                    }
                    customizable_options {
                        label
                        id
                          values {
                            value
                        }
                    }
                }
            }
        },
        user_errors {
            message
        }
    }
}
MUTATION;
    }
}
