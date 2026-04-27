<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ProductVideo;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Gallery\DefaultValueProcessor;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\ScopeFixture;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Test for \Magento\ProductVideo feature
 *
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductVideoExternalSourceTest extends WebapiAbstract
{
    public const SERVICE_NAME = 'catalogProductRepositoryV1';
    public const SERVICE_VERSION = 'V1';
    public const RESOURCE_PATH = '/V1/products';

    /**
     * Media gallery entries with external videos
     *
     * @return array
     */
    public static function externalVideoDataProvider(): array
    {
        return [
            'youtube-external-video' => [
                [
                    'media_type' => 'external-video',
                    'disabled' => false,
                    'label' => 'Test Video Created',
                    'types' => [],
                    'position' => 1,
                    'content' => self::getVideoThumbnailStub(),
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
                    'content' => self::getVideoThumbnailStub(),
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
    private static function getVideoThumbnailStub(): array
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
     * @param array $mediaGalleryData
     */
    #[DataProvider('externalVideoDataProvider')]
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

    #[
        DataFixture(ScopeFixture::class, as: 'global_scope'),
        DataFixture(StoreFixture::class, as: 'store_view_2'),
        DataFixture(
            ProductFixture::class,
            [
                'media_gallery_entries' => [
                    [
                        'media_type' => 'external-video',
                        'disabled' => false,
                        'label' => 'Test Video Updated',
                        'position' => 1,
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
            ],
            as: 'p1',
            scope: 'global_scope'
        ),
    ]
    public function testMediaGalleryInheritanceTest(): void
    {
        $this->_markTestAsRestOnly(
            'Test skipped due to known issue with SOAP. NULL value is cast to corresponding attribute type.'
        );

        $fixtures = DataFixtureStorageManager::getStorage();
        $defaultValueProcessor = Bootstrap::getObjectManager()->get(DefaultValueProcessor::class);
        $sku = $fixtures->get('p1')->getSku();
        $store2 = $fixtures->get('store_view_2');

        $productData = $this->getProductData($sku, $store2->getCode());

        // Update1: Update product in store view 2 without media_gallery_entries
        $update1 = $productData;
        unset($update1['media_gallery_entries']);
        $this->saveProduct($update1, $store2->getCode());

        // Check video label, visibility and position inheritance in store view 2
        $product = $this->getProductModel($sku, (int) $store2->getId());
        $gallery = $defaultValueProcessor->process($product, $product->getData('media_gallery'));
        $video = current($gallery['images']);
        $this->assertEquals(1, $video['label_use_default']);
        $this->assertEquals(1, $video['disabled_use_default']);
        $this->assertEquals(1, $video['position_use_default']);
        $this->assertEquals(1, $video['video_title_use_default']);
        $this->assertEquals(1, $video['video_description_use_default']);

        // Update2: Update product in store view 2 with media_gallery_entries
        $update2 = $productData;
        $this->saveProduct($update2, $store2->getCode());

        // Check video label, visibility and position inheritance in store view 2
        $product = $this->getProductModel($sku, (int) $store2->getId());
        $gallery = $defaultValueProcessor->process($product, $product->getData('media_gallery'));
        $video = current($gallery['images']);
        $this->assertEquals(0, $video['label_use_default']);
        $this->assertEquals(0, $video['disabled_use_default']);
        $this->assertEquals(0, $video['position_use_default']);
        $this->assertEquals(0, $video['video_title_use_default']);
        $this->assertEquals(0, $video['video_description_use_default']);

        // Update3: Update product in store view 2 to use default values for media_gallery_entries
        $update3 = $productData;
        foreach ($update3['media_gallery_entries'] as &$entry) {
            $entry['label'] = null;
            $entry['position'] = null;
            $entry['disabled'] = null;
            $entry['extension_attributes']['video_content']['video_title'] = null;
            $entry['extension_attributes']['video_content']['video_description'] = null;
        }
        $this->saveProduct($update3, $store2->getCode());

        // Check video label, visibility and position inheritance in store view 2
        $product = $this->getProductModel($sku, (int) $store2->getId());
        $gallery = $defaultValueProcessor->process($product, $product->getData('media_gallery'));
        $video = current($gallery['images']);
        $this->assertEquals(1, $video['label_use_default']);
        $this->assertEquals(1, $video['disabled_use_default']);
        $this->assertEquals(1, $video['position_use_default']);
        $this->assertEquals(1, $video['video_title_use_default']);
        $this->assertEquals(1, $video['video_description_use_default']);
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

    private function getProductModel(string $sku, ?int $storeId = null): ProductInterface
    {
        try {
            $productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
            $product = $productRepository->get($sku, false, $storeId, true);
        } catch (NoSuchEntityException $e) {
            $product = null;
            $this->fail("Couldn`t load product: {$sku}");
        }
        return $product;
    }
    
    private function getProductData(string $sku, ?string $storeCode = null): array
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $sku,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];

        return $this->_webApiCall($serviceInfo, ['sku' => $sku], null, $storeCode);
    }
}
