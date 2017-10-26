<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\GraphQl;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class ProductViewTest extends GraphQlAbstract
{
    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_all_fields.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testQueryAllFieldsSimpleProduct()
    {
        $prductSku = 'simple';

        $query = <<<QUERY
{
    product(sku: "{$prductSku}")
    {
        attribute_set_id
        category_ids
        category_links
        {
            category_id
            position
        }
        country_of_manufacture
        created_at
        custom_design
        custom_design_from
        custom_design_to
        custom_layout
        custom_layout_update
        description
        gallery
        gift_message_available
        has_options
        id
        image
        image_label
        links_exist
        links_purchased_separately
        links_title
        media_gallery
        meta_description
        meta_keyword
        meta_title
        media_gallery_entries
        {
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
        minimal_price
        msrp
        msrp_display_actual_price_type
        name
        news_from_date
        news_to_date
        old_id
        options_container
        options
        {
            file_extension
            image_size_x
            image_size_y
            is_require
            max_characters
            option_id
            price
            price_type
            product_sku
            sku
            sort_order
            title
            type
            values
            {
                title
                sort_order
                price
                price_type
                sku
                option_type_id
            }
        }
        page_layout
        price
        price_type
        price_view
        product_links
        {
            link_type
            linked_product_sku
            linked_product_type
            position
            qty
            sku
        }
        required_options
        samples_title
        shipment_type
        short_description
        sku
        small_image
        small_image_label
        special_from_date
        special_price
        special_to_date
        status
        swatch_image
        tax_class_id
        thumbnail
        thumbnail_label
        tier_price
        tier_prices
        {
            customer_group_id
            percentage_value
            qty
            value
            website_id
        }
        type_id
        updated_at
        url_key
        url_path
        visibility
        website_ids
        weight
        weight_type
    }
}
QUERY;

        $response = $this->graphQlQuery($query);

        /**
         * @var ProductRepositoryInterface $productRepository
         */

        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($prductSku, false, null, true);
        $this->assertArrayHasKey('product', $response);
        $this->assertBaseFields($product, $response['product']);
        $this->assertEavAttributes($product, $response['product']);
        $this->assertCategoryIds($product, $response['product']);
        $this->assertOptions($product, $response['product']);
        $this->assertTierPrices($product, $response['product']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_media_gallery_entries.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testQueryMediaGalleryEntryFieldsSimpleProduct()
    {

        $prductSku = 'simple';

        $query = <<<QUERY
{
    product(sku: "{$prductSku}")
    {
        attribute_set_id
        category_ids
        category_links
        {
            category_id
            position
        }
        country_of_manufacture
        created_at
        custom_design
        custom_design_from
        custom_design_to
        custom_layout
        custom_layout_update
        description
        gallery
        gift_message_available
        has_options
        id
        image
        image_label
        links_exist
        links_purchased_separately
        links_title
        media_gallery
        meta_description
        meta_keyword
        meta_title
        media_gallery_entries
        {
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
        minimal_price
        msrp
        msrp_display_actual_price_type
        name
        news_from_date
        news_to_date
        old_id
        options_container
        options
        {
            file_extension
            image_size_x
            image_size_y
            is_require
            max_characters
            option_id
            price
            price_type
            product_sku
            sku
            sort_order
            title
            type
            values
            {
                title
                sort_order
                price
                price_type
                sku
                option_type_id
            }
        }
        page_layout
        price
        price_type
        price_view
        product_links
        {
            link_type
            linked_product_sku
            linked_product_type
            position
            qty
            sku
        }
        required_options
        samples_title
        shipment_type
        short_description
        sku
        small_image
        small_image_label
        special_from_date
        special_price
        special_to_date
        status
        swatch_image
        tax_class_id
        thumbnail
        thumbnail_label
        tier_price
        tier_prices
        {
            customer_group_id
            percentage_value
            qty
            value
            website_id
        }
        type_id
        updated_at
        url_key
        url_path
        visibility
        website_ids
        weight
        weight_type
    }
}
QUERY;

        $response = $this->graphQlQuery($query);

        /**
         * @var ProductRepositoryInterface $productRepository
         */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($prductSku, false, null, true);
        $this->assertMediaGalleryEntries($product, $response['product']);
    }

    /**
     * @param ProductInterface $product
     * @param array $actualResponse
     */
    private function assertMediaGalleryEntries($product, $actualResponse)
    {
        $mediaGalleryEntries = $product->getMediaGalleryEntries();
        $this->assertCount(1, $mediaGalleryEntries, "Precondition failed, incorrect number of media gallery entries.");
        $this->assertTrue(
            is_array([$actualResponse['media_gallery_entries']]),
            "Media galleries field must be of an array type."
        );
        $this->assertCount(1, $actualResponse['media_gallery_entries'], "There must be 1 record in media gallery.");
        $mediaGalleryEntry = $mediaGalleryEntries[0];
        $this->assertResponseFields(
            $actualResponse['media_gallery_entries'][0],
            [
                'disabled' => (bool)$mediaGalleryEntry->isDisabled(),
                'file' => $mediaGalleryEntry->getFile(),
                'id' => $mediaGalleryEntry->getId(),
                'label' => $mediaGalleryEntry->getLabel(),
                'media_type' => $mediaGalleryEntry->getMediaType(),
                'position' => $mediaGalleryEntry->getPosition(),
            ]
        );
        $videoContent = $mediaGalleryEntry->getExtensionAttributes()->getVideoContent();
        $this->assertResponseFields(
            $actualResponse['media_gallery_entries'][0]['video_content'],
            [
                'media_type' => $videoContent->getMediaType(),
                'video_description' => $videoContent->getVideoDescription(),
                'video_metadata' => $videoContent->getVideoMetadata(),
                'video_provider' => $videoContent->getVideoProvider(),
                'video_title' => $videoContent->getVideoTitle(),
                'video_url' => $videoContent->getVideoUrl(),
            ]
        );
    }

    /**
     * @param ProductInterface $product
     * @param array $actualResponse
     */
    private function assertCategoryIds($product, $actualResponse)
    {
        $categoryIdsAttribute = $product->getCustomAttribute('category_ids');
        $this->assertNotEmpty($categoryIdsAttribute, "Precondition failed: 'category_ids' must not be empty");
        $categoryIdsAttributeValue = $categoryIdsAttribute ? $categoryIdsAttribute->getValue() : [];
        $expectedValue = implode(',', $categoryIdsAttributeValue);
        $this->assertEquals($expectedValue, $actualResponse['category_ids']);
    }

    /**
     * @param ProductInterface $product
     * @param $actualResponse
     */
    private function assertTierPrices($product, $actualResponse)
    {
        $tierPrices = $product->getTierPrices();
        $this->assertNotEmpty($actualResponse['tier_prices'], "Precondition failed: 'tier_prices' must not be empty");
        foreach ($actualResponse['tier_prices'] as $tierPriceIndex => $tierPriceArray) {
            foreach ($tierPriceArray as $key => $value) {
                /**
                 * @var \Magento\Catalog\Model\Product\TierPrice $tierPrice
                 */
                $tierPrice = $tierPrices[$tierPriceIndex];
                $this->assertEquals($value, $tierPrice->getData($key));
            }
        }
    }

    /**
     * @param ProductInterface $product
     * @param $actualResponse
     */
    private function assertOptions($product, $actualResponse)
    {
        $productOptions = $product->getOptions();
        $this->assertNotEmpty($actualResponse['options'], "Precondition failed: 'options' must not be empty");
        foreach ($actualResponse['options'] as $optionsIndex => $optionsArray) {
            /** @var \Magento\Catalog\Model\Product\Option $option */
            $option = $productOptions[$optionsIndex];
            $assertionMap = [
                ['response_field' => 'product_sku', 'expected_value' => $option->getProductSku()],
                ['response_field' => 'sort_order', 'expected_value' => $option->getSortOrder()],
                ['response_field' => 'title', 'expected_value' => $option->getTitle()],
                ['response_field' => 'type', 'expected_value' => $option->getType()],
                ['response_field' => 'option_id', 'expected_value' => $option->getOptionId()],
                ['response_field' => 'is_require', 'expected_value' => $option->getIsRequire()],
                ['response_field' => 'sort_order', 'expected_value' => $option->getSortOrder()]
            ];

            if (!empty($option->getValues())) {
                $value = current($optionsArray['values']);
                /** @var \Magento\Catalog\Model\Product\Option\Value $productValue */
                $productValue = current($option->getValues());
                $assertionMapValues = [
                    ['response_field' => 'title', 'expected_value' => $productValue->getTitle()],
                    ['response_field' => 'sort_order', 'expected_value' => $productValue->getSortOrder()],
                    ['response_field' => 'price', 'expected_value' => $productValue->getPrice()],
                    ['response_field' => 'price_type', 'expected_value' => $productValue->getPriceType()],
                    ['response_field' => 'sku', 'expected_value' => $productValue->getSku()],
                    ['response_field' => 'option_type_id', 'expected_value' => $productValue->getOptionTypeId()]
                ];
                $this->assertResponseFields($value, $assertionMapValues);
            } else {
                if ($option->getType() === 'file') {
                    $assertionMap = array_merge(
                        $assertionMap,
                        [
                            ['response_field' => 'file_extension', 'expected_value' => $option->getFileExtension()],
                            ['response_field' => 'image_size_x', 'expected_value' => $option->getImageSizeX()],
                            ['response_field' => 'image_size_y', 'expected_value' => $option->getImageSizeY()]
                        ]
                    );
                } elseif ($option->getType() === 'area') {
                    $assertionMap = array_merge(
                        $assertionMap,
                        [
                            ['response_field' => 'max_characters', 'expected_value' => $option->getMaxCharacters()],
                        ]
                    );
                }

                $assertionMap = array_merge(
                    $assertionMap,
                    [
                        ['response_field' => 'price', 'expected_value' => $option->getPrice()],
                        ['response_field' => 'price_type', 'expected_value' => $option->getPriceType()],
                        ['response_field' => 'sku', 'expected_value' => $option->getSku()]
                    ]
                );
            }
            $this->assertResponseFields($optionsArray, $assertionMap);
        }
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
            ['response_field' => 'price', 'expected_value' => $product->getPrice()],
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
     * @param ProductInterface $product
     * @param array $actualResponse
     */
    private function assertEavAttributes($product, $actualResponse)
    {
        $eavAttributes = [
            'url_key',
            'description',
            'has_options',
            'meta_description',
            'meta_keyword',
            'meta_title',
            'short_description',
            'tax_class_id',
            'country_of_manufacture',
            'msrp',
            'gift_message_available',
            'has_options',
            'minimal_price',
            'msrp_display_actual_price_type',
            'news_from_date',
            'old_id',
            'options_container',
            'required_options',
            'special_price',
            'special_from_date',
            'special_to_date',
        ];
        $assertionMap = [];
        foreach ($eavAttributes as $attributeCode) {
            $expectedAttribute = $product->getCustomAttribute($attributeCode);
            $assertionMap[] = [
                'response_field' => $attributeCode,
                'expected_value' => $expectedAttribute ? $expectedAttribute->getValue() : null
            ];
        }

        $this->assertResponseFields($actualResponse, $assertionMap);
    }

    /**
     * @param array $actualResponse
     * @param array $assertionMap ['response_field_name' => 'response_field_value', ...]
     *                         OR [['response_field' => $field, 'expected_value' => $value], ...]
     */
    private function assertResponseFields($actualResponse, $assertionMap)
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
