<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\GraphQl;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class ConfigurableProductViewTest extends GraphQlAbstract
{
    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable_with_category_and_weight.php
     */
    public function testQueryConfigurableProductLinks()
    {
        $productSku = 'configurable';

        $query
            = <<<QUERY
{
    product(sku: "{$productSku}")
    {
        id
        attribute_set_id
        created_at
        name
        sku
        status
        type_id
        updated_at
        visibility
        weight
        category_ids                
        ... on ConfigurableProduct {
            configurable_product_links {
                id
                category_ids
                name
                sku
                attribute_set_id
                visibility
                weight
                created_at
                updated_at
                category_links {
                position
                category_id
                }
                media_gallery_entries{
                disabled
                file
                id
                label
                media_type
                position
                types
                content
                {
                    base64_encoded_data
                    type
                    name
                }
                video_content
                {
                    media_type
                    video_description
                    video_metadata
                    video_provider
                    video_title
                    video_url
                }            
              }
            }
        }
    }
    
}
QUERY;

        $response = $this->graphQlQuery($query);

        /**
         * @var ProductRepositoryInterface $productRepository
         */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($productSku, false, null, true);

        $this->assertArrayHasKey('product', $response);
        $this->assertBaseFields($product, $response['product']);
        $this->assertConfigurableProductLinks($response['product']);
    }

    /**
     * @param ProductInterface $product
     * @param array $actualResponse
     */
    private function assertBaseFields($product, $actualResponse)
    {
        /**
         * ['product_object_field_name', 'expected_value']
         */
        $assertionMap = [
            ['response_field' => 'attribute_set_id', 'expected_value' => $product->getAttributeSetId()],
            ['response_field' => 'created_at', 'expected_value' => $product->getCreatedAt()],
            ['response_field' => 'id', 'expected_value' => $product->getId()],
            ['response_field' => 'name', 'expected_value' => $product->getName()],
            ['response_field' => 'sku', 'expected_value' => $product->getSku()],
            ['response_field' => 'status', 'expected_value' => $product->getStatus()],
            ['response_field' => 'type_id', 'expected_value' => $product->getTypeId()],
            ['response_field' => 'updated_at', 'expected_value' => $product->getUpdatedAt()],
            ['response_field' => 'visibility', 'expected_value' => $product->getVisibility()],
            ['response_field' => 'weight', 'expected_value' => $product->getWeight()],
        ];

        $this->assertResponseFields($actualResponse, $assertionMap);
    }

    /**
     * Asserts various fields for child products for a configurable products
     *
     * @param $actualResponse
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function assertConfigurableProductLinks($actualResponse)
    {
        $this->assertNotEmpty(
            $actualResponse['configurable_product_links'],
            "Precondition failed: 'configurable_product_links' must not be empty"
        );
        foreach ($actualResponse[
            'configurable_product_links'
                 ] as $configurableProductLinkIndex => $configurableProductLinkArray) {
            $this->assertNotEmpty($configurableProductLinkArray);
            $this->assertTrue(
                isset($configurableProductLinkArray['id']),
                'configurable_product_links elements don\'t contain id key'
            );
            $indexValue = $configurableProductLinkArray['id'];
            unset($configurableProductLinkArray['id']);
            $this->assertTrue(
                isset($configurableProductLinkArray['category_ids']),
                'configurable_product_links doesn\'t contain category_ids key'
            );
            $this->assertTrue(
                isset($configurableProductLinkArray['category_links']),
                'configurable_product_links doesn\'t contain category_links key'
            );
            $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
            /** @var \Magento\Catalog\Model\Product $childProduct */
            $childProduct = $productRepository->getById($indexValue);

            /** @var  \Magento\Catalog\Api\Data\ProductLinkInterface[] */
            $links = $childProduct->getExtensionAttributes()->getCategoryLinks();
            $this->assertCount(1, $links, "Precondition failed, incorrect number of category_links.");
            $position =$links[0]->getPosition();
            $categoryId = $links[0]->getCategoryId();

            $actualValue
                = $actualResponse['configurable_product_links'][$configurableProductLinkIndex]['category_links'][0];
            $this->assertEquals($actualValue, ['position' => $position, 'category_id' =>$categoryId]);
            unset($configurableProductLinkArray['category_links']);

            $categoryIdsAttribute = $childProduct->getCustomAttribute('category_ids');
            $this->assertNotEmpty($categoryIdsAttribute, "Precondition failed: 'category_ids' must not be empty");
            $categoryIdsAttributeValue = $categoryIdsAttribute ? $categoryIdsAttribute->getValue() : [];
            $expectedValue = implode(',', $categoryIdsAttributeValue);
            $this->assertEquals($expectedValue, $actualResponse['category_ids']);
            unset($configurableProductLinkArray['category_ids']);

            $mediaGalleryEntries = $childProduct->getMediaGalleryEntries();
            $this->assertCount(
                1,
                $mediaGalleryEntries,
                "Precondition failed since there are incorrect number of media gallery entries"
            );
            $this->assertTrue(
                is_array(
                    $actualResponse['configurable_product_links']
                    [$configurableProductLinkIndex]
                    ['media_gallery_entries']
                )
            );
            $this->assertCount(
                1,
                $actualResponse['configurable_product_links'][$configurableProductLinkIndex]['media_gallery_entries'],
                "there must be 1 record in the media gallery"
            );
            $mediaGalleryEntry = $mediaGalleryEntries[0];
            $this->assertResponseFields(
                $actualResponse['configurable_product_links']
                [$configurableProductLinkIndex]
                ['media_gallery_entries'][0],
                [
                    'disabled' => (bool)$mediaGalleryEntry->isDisabled(),
                    'file' => $mediaGalleryEntry->getFile(),
                    'id' => $mediaGalleryEntry->getId(),
                    'label' => $mediaGalleryEntry->getLabel(),
                    'media_type' => $mediaGalleryEntry->getMediaType(),
                    'position' => $mediaGalleryEntry->getPosition()
                ]
            );
            $videoContent = $mediaGalleryEntry->getExtensionAttributes()->getVideoContent();
            $this->assertResponseFields(
                $actualResponse['configurable_product_links']
                [$configurableProductLinkIndex]
                ['media_gallery_entries']
                [0]
                ['video_content'],
                [
                    'media_type' =>$videoContent->getMediaType(),
                    'video_description' => $videoContent->getVideoDescription(),
                    'video_metadata' =>$videoContent->getVideoMetadata(),
                    'video_provider' => $videoContent->getVideoProvider(),
                    'video_title' => $videoContent->getVideoTitle(),
                    'video_url' => $videoContent->getVideoUrl()
                ]
            );
            unset($configurableProductLinkArray['media_gallery_entries']);

            foreach ($configurableProductLinkArray as $key => $value) {
                $this->assertEquals($value, $childProduct->getData($key));
            }
        }
    }

    /**
     * @param array $actualResponse
     * @param array $assertionMap ['response_field_name' => 'response_field_value', ...]
     *                         OR [['response_field' => $field, 'expected_value' => $value], ...]
     */
    private function assertResponseFields(array $actualResponse, array $assertionMap)
    {
        foreach ($assertionMap as $key => $assertionData) {
            $expectedValue = isset($assertionData['expected_value'])
                ? $assertionData['expected_value']
                : $assertionData;
            $responseField = isset($assertionData['response_field']) ? $assertionData['response_field'] : $key;
            $this->assertNotNull(
                $expectedValue,
                "Value of '{$responseField}' field must not be NULL"
            );
            $this->assertEquals(
                $expectedValue,
                $actualResponse[$responseField],
                "Value of '{$responseField}' field in response does not match expected value: "
                . var_export($expectedValue, true)
            );
        }
    }
}
