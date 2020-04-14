<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductVideo;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Test for \Magento\ProductVideo feature
 *
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductVideoExternalSourceTest extends WebapiAbstract
{
    const SERVICE_NAME = 'catalogProductRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/products';

    /**
     * Media gallery entries with external videos
     *
     * @return array
     */
    public function externalVideoDataProvider(): array
    {
        return [
            'youtube-external-video' => [
                [
                    'media_type' => 'external-video',
                    'disabled' => false,
                    'label' => 'Test Video Created',
                    'types' => [],
                    'position' => 1,
                    'content' => $this->getVideoThumbnailStub(),
                    'extension_attributes' => [
                        'video_content' => [
                            'media_type' => 'external-video',
                            'video_provider' => 'youtube',
                            'video_url' => 'https://www.youtube.com/',
                            'video_title' => 'Video title',
                            'video_description' => 'Video description',
                            'video_metadata' => 'Video meta',
                        ],
                    ],
                ]
            ],
            'vimeo-external-video' => [
                [
                    'media_type' => 'external-video',
                    'disabled' => false,
                    'label' => 'Test Video Updated',
                    'types' => [],
                    'position' => 1,
                    'content' => $this->getVideoThumbnailStub(),
                    'extension_attributes' => [
                        'video_content' => [
                            'media_type' => 'external-video',
                            'video_provider' => 'vimeo',
                            'video_url' => 'https://www.vimeo.com/',
                            'video_title' => 'Video title',
                            'video_description' => 'Video description',
                            'video_metadata' => 'Video meta',
                        ],
                    ],
                ]
            ]
        ];
    }

    /**
     * Returns the array of data for Video thumbnail
     *
     * @return array|string[]
     */
    private function getVideoThumbnailStub(): array
    {
        return [
            'type' => 'image/png',
            'name' => 'thumbnail.png',
            'base64_encoded_data' => 'iVBORw0KGgoAAAANSUhEUgAAAP8AAADGCAMAAAAqo6adAAAAA1BMVEUAAP79f'
                . '+LBAAAASElEQVR4nO3BMQEAAADCoPVPbQwfoAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'
                . 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAA+BsYAAAF7hZJ0AAAAAElFTkSuQmCC',
        ];
    }

    /**
     * Test create/ update product with external video media gallery entry
     *
     * @dataProvider externalVideoDataProvider
     * @param array $mediaGalleryData
     */
    public function testCreateWithExternalVideo(array $mediaGalleryData)
    {
        $simpleProductBaseData = $this->getSimpleProductData(
            [
                ProductInterface::NAME => 'Product With Ext. Video',
                ProductInterface::SKU => 'prod-with-ext-video'
            ]
        );

        $simpleProductBaseData['media_gallery_entries'] = [$mediaGalleryData];

        $response = $this->saveProduct($simpleProductBaseData);
        $this->assertEquals(
            $simpleProductBaseData['media_gallery_entries'][0]['extension_attributes'],
            $response["media_gallery_entries"][0]["extension_attributes"]
        );
    }

    /**
     * Get Simple Product Data
     *
     * @param array $productData
     * @return array
     */
    protected function getSimpleProductData($productData = [])
    {
        return [
            ProductInterface::SKU => isset($productData[ProductInterface::SKU])
                ? $productData[ProductInterface::SKU] : uniqid('sku-', true),
            ProductInterface::NAME => isset($productData[ProductInterface::NAME])
                ? $productData[ProductInterface::NAME] : uniqid('sku-', true),
            ProductInterface::VISIBILITY => 4,
            ProductInterface::TYPE_ID => 'simple',
            ProductInterface::PRICE => 3.62,
            ProductInterface::STATUS => 1,
            ProductInterface::ATTRIBUTE_SET_ID => 4,
            'custom_attributes' => [
                ['attribute_code' => 'cost', 'value' => ''],
                ['attribute_code' => 'description', 'value' => 'Description'],
            ]
        ];
    }

    /**
     * Save Product
     *
     * @param $product
     * @param string|null $storeCode
     * @param string|null $token
     * @return mixed
     */
    protected function saveProduct($product, $storeCode = null, ?string $token = null)
    {
        if (isset($product['custom_attributes'])) {
            foreach ($product['custom_attributes'] as &$attribute) {
                if ($attribute['attribute_code'] == 'category_ids'
                    && !is_array($attribute['value'])
                ) {
                    $attribute['value'] = [""];
                }
            }
        }
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        if ($token) {
            $serviceInfo['rest']['token'] = $serviceInfo['soap']['token'] = $token;
        }
        $requestData = ['product' => $product];

        return $this->_webApiCall($serviceInfo, $requestData, null, $storeCode);
    }
}
