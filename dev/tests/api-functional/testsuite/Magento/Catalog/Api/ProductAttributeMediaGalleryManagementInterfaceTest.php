<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Api;

use Magento\Framework\Api\Data\ImageContentInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product\Attribute\Backend\Media\ImageEntryConverter;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class ProductAttributeMediaGalleryManagementInterfaceTest
 */
class ProductAttributeMediaGalleryManagementInterfaceTest extends WebapiAbstract
{
    /**
     * Default create service request information (product with SKU 'simple' is used)
     *
     * @var array
     */
    protected $createServiceInfo;

    /**
     * Default update service request information (product with SKU 'simple' is used)
     *
     * @var array
     */
    protected $updateServiceInfo;

    /**
     * Default delete service request information (product with SKU 'simple' is used)
     *
     * @var array
     */
    protected $deleteServiceInfo;

    /**
     * @var string
     */
    protected $testImagePath;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->createServiceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/simple/media',
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => 'catalogProductAttributeMediaGalleryManagementV1',
                'serviceVersion' => 'V1',
                'operation' => 'catalogProductAttributeMediaGalleryManagementV1Create',
            ],
        ];

        $this->updateServiceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/simple/media',
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => 'catalogProductAttributeMediaGalleryManagementV1',
                'serviceVersion' => 'V1',
                'operation' => 'catalogProductAttributeMediaGalleryManagementV1Update',
            ],
        ];

        $this->deleteServiceInfo = [
            'rest' => [
                'httpMethod' => Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => 'catalogProductAttributeMediaGalleryManagementV1',
                'serviceVersion' => 'V1',
                'operation' => 'catalogProductAttributeMediaGalleryManagementV1Remove',
            ],
        ];

        $this->testImagePath = __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'test_image.jpg';
    }

    /**
     * Retrieve product that was updated by test
     *
     * @return \Magento\Catalog\Model\Product
     */
    protected function getTargetSimpleProduct()
    {
        return $this->objectManager->get(ProductFactory::class)->create()->load(1);
    }

    /**
     * Retrieve target product image ID
     *
     * Target product must have single image if this function is used
     *
     * @return int
     */
    protected function getTargetGalleryEntryId()
    {
        $mediaGallery = $this->getTargetSimpleProduct()->getData('media_gallery');
        $image = array_shift($mediaGallery['images']);

        return (int)$image['value_id'];
    }

    /**
     * Test create() method
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testCreate()
    {
        $requestData = [
            'id' => null,
            'media_type' => ImageEntryConverter::MEDIA_TYPE_CODE,
            'label' => 'Image Text',
            'position' => 1,
            'types' => ['image'],
            'disabled' => false,
            'content' => [
                ImageContentInterface::BASE64_ENCODED_DATA => base64_encode(file_get_contents($this->testImagePath)),
                ImageContentInterface::TYPE => 'image/jpeg',
                ImageContentInterface::NAME => 'test_image.jpg'
            ]
        ];

        $actualResult = $this->_webApiCall($this->createServiceInfo, ['sku' => 'simple', 'entry' => $requestData]);
        $targetProduct = $this->getTargetSimpleProduct();
        $mediaGallery = $targetProduct->getData('media_gallery');

        $this->assertCount(1, $mediaGallery['images']);
        $updatedImage = array_shift($mediaGallery['images']);
        $this->assertEquals($actualResult, $updatedImage['value_id']);
        $this->assertEquals('Image Text', $updatedImage['label']);
        $this->assertEquals(1, $updatedImage['position']);
        $this->assertEquals(0, $updatedImage['disabled']);
        $this->assertStringStartsWith('/t/e/test_image', $updatedImage['file']);
        $this->assertEquals($updatedImage['file'], $targetProduct->getData('image'));
    }

    /**
     * Test create() method without file
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testCreateWithoutFileExtension()
    {
        $requestData = [
            'id' => null,
            'media_type' => ImageEntryConverter::MEDIA_TYPE_CODE,
            'label' => 'Image Text',
            'position' => 1,
            'types' => ['image'],
            'disabled' => false,
            'content' => [
                ImageContentInterface::BASE64_ENCODED_DATA => base64_encode(file_get_contents($this->testImagePath)),
                ImageContentInterface::TYPE => 'image/jpeg',
                ImageContentInterface::NAME => 'test_image'
            ]
        ];

        $actualResult = $this->_webApiCall($this->createServiceInfo, ['sku' => 'simple', 'entry' => $requestData]);
        $targetProduct = $this->getTargetSimpleProduct();
        $mediaGallery = $targetProduct->getData('media_gallery');

        $this->assertCount(1, $mediaGallery['images']);
        $updatedImage = array_shift($mediaGallery['images']);
        $this->assertEquals($actualResult, $updatedImage['value_id']);
        $this->assertEquals('Image Text', $updatedImage['label']);
        $this->assertEquals(1, $updatedImage['position']);
        $this->assertEquals(0, $updatedImage['disabled']);
        $this->assertStringStartsWith('/t/e/test_image', $updatedImage['file']);
        $this->assertEquals($updatedImage['file'], $targetProduct->getData('image'));
    }

    /**
     * Test create() method with not default store id
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testCreateWithNotDefaultStoreId()
    {
        $requestData = [
            'id' => null,
            'media_type' => ImageEntryConverter::MEDIA_TYPE_CODE,
            'label' => 'Image Text',
            'position' => 1,
            'types' => ['image'],
            'disabled' => false,
            'content' => [
                'base64_encoded_data' => base64_encode(file_get_contents($this->testImagePath)),
                'type' => 'image/jpeg',
                'name' => 'test_image.jpg',
            ]
        ];

        $actualResult = $this->_webApiCall(
            $this->createServiceInfo,
            [
                'sku' => 'simple',
                'entry' => $requestData,
                'storeId' => 1,
            ]
        );
        $targetProduct = $this->getTargetSimpleProduct();
        $mediaGallery = $targetProduct->getData('media_gallery');
        $this->assertCount(1, $mediaGallery['images']);
        $updatedImage = array_shift($mediaGallery['images']);
        // Values for not default store view were provided
        $this->assertEquals('Image Text', $updatedImage['label']);
        $this->assertEquals($actualResult, $updatedImage['value_id']);
        $this->assertEquals(1, $updatedImage['position']);
        $this->assertEquals(0, $updatedImage['disabled']);
        $this->assertStringStartsWith('/t/e/test_image', $updatedImage['file']);
        $this->assertEquals($updatedImage['file'], $targetProduct->getData('image'));
        // No values for default store view were provided
        $this->assertNull($updatedImage['label_default']);
        $this->assertNull($updatedImage['position_default']);
        $this->assertNull($updatedImage['disabled_default']);
    }

    /**
     * Test update() method
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_image.php
     */
    public function testUpdate()
    {
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $productRepository->get('simple');
        $imageId = (int)$product->getMediaGalleryImages()->getFirstItem()->getValueId();
        $requestData = [
            'sku' => 'simple',
            'entry' => [
                'id' => $this->getTargetGalleryEntryId(),
                'label' => 'Updated Image Text',
                'position' => 10,
                'types' => ['thumbnail'],
                'disabled' => true,
                'media_type' => 'image',
            ],
        ];

        $this->updateServiceInfo['rest']['resourcePath'] = $this->updateServiceInfo['rest']['resourcePath']
            . '/' . $this->getTargetGalleryEntryId();

        $this->assertTrue($this->_webApiCall($this->updateServiceInfo, $requestData, null, 'all'));
        $updatedImage = $this->assertMediaGalleryData($imageId, '/m/a/magento_image.jpg', 'Updated Image Text');
        $this->assertEquals(10, $updatedImage['position_default']);
        $this->assertEquals(1, $updatedImage['disabled_default']);
    }

    /**
     * Update media gallery entity with new image.
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_image.php
     * @return void
     */
    public function testUpdateWithNewImage(): void
    {
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $productRepository->get('simple');
        $imageId = (int)$product->getMediaGalleryImages()->getFirstItem()->getValueId();

        $requestData = [
            'sku' => 'simple',
            'entry' => [
                'id' => $this->getTargetGalleryEntryId(),
                'label' => 'Updated Image Text',
                'position' => 10,
                'types' => ['thumbnail'],
                'disabled' => true,
                'media_type' => 'image',
                'content' => [
                    'base64_encoded_data' => 'iVBORw0KGgoAAAANSUhEUgAAAP8AAADGCAMAAAAqo6adAAAAA1BMVEUAAP79f'
                        . '+LBAAAASElEQVR4nO3BMQEAAADCoPVPbQwfoAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'
                        . 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAA+BsYAAAF7hZJ0AAAAAElFTkSuQmCC',
                    'type' => 'image/png',
                    'name' => 'testname_updated.png',
                ],
            ],
        ];

        $this->updateServiceInfo['rest']['resourcePath'] = $this->updateServiceInfo['rest']['resourcePath']
            . '/' . $this->getTargetGalleryEntryId();

        $this->assertTrue($this->_webApiCall($this->updateServiceInfo, $requestData, null, 'all'));
        $updatedImage = $this->assertMediaGalleryData($imageId, '/t/e/testname_updated.png', 'Updated Image Text');
        $this->assertEquals(10, $updatedImage['position_default']);
        $this->assertEquals(1, $updatedImage['disabled_default']);
    }

    /**
     * Test update() method with not default store id
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_image.php
     */
    public function testUpdateWithNotDefaultStoreId()
    {
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $productRepository->get('simple');
        $imageId = (int)$product->getMediaGalleryImages()->getFirstItem()->getValueId();

        $requestData = [
            'sku' => 'simple',
            'entry' => [
                'id' => $this->getTargetGalleryEntryId(),
                'label' => 'Updated Image Text',
                'position' => 10,
                'types' => ['thumbnail'],
                'disabled' => true,
                'media_type' => 'image',
            ]
        ];

        $this->updateServiceInfo['rest']['resourcePath'] = $this->updateServiceInfo['rest']['resourcePath']
            . '/' . $this->getTargetGalleryEntryId();

        $this->assertTrue($this->_webApiCall($this->updateServiceInfo, $requestData, null, 'default'));
        $updatedImage = $this->assertMediaGalleryData($imageId, '/m/a/magento_image.jpg', 'Image Alt Text');
        $this->assertEquals(1, $updatedImage['position_default']);
        $this->assertEquals(0, $updatedImage['disabled_default']);
    }

    /**
     * Check that Media Gallery data is correct.
     *
     * @param int $imageId
     * @param string $file
     * @param string $label
     * @return array
     */
    private function assertMediaGalleryData(int $imageId, string $file, string $label): array
    {
        $targetProduct = $this->getTargetSimpleProduct();
        $this->assertEquals($file, $targetProduct->getData('thumbnail'));
        $this->assertEquals('no_selection', $targetProduct->getData('image'));
        $this->assertEquals('no_selection', $targetProduct->getData('small_image'));
        $mediaGallery = $targetProduct->getData('media_gallery');
        $this->assertCount(1, $mediaGallery['images']);
        $updatedImage = array_shift($mediaGallery['images']);
        $this->assertEquals($imageId, $updatedImage['value_id']);
        $this->assertEquals('Updated Image Text', $updatedImage['label']);
        $this->assertEquals($file, $updatedImage['file']);
        $this->assertEquals(10, $updatedImage['position']);
        $this->assertEquals(1, $updatedImage['disabled']);
        $this->assertEquals($label, $updatedImage['label_default']);

        return $updatedImage;
    }

    /**
     * Test delete() method
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_image_without_types.php
     */
    public function testDelete()
    {
        $entryId = $this->getTargetGalleryEntryId();
        $this->deleteServiceInfo['rest']['resourcePath'] = "/V1/products/simple/media/{$entryId}";
        $requestData = [
            'sku' => 'simple',
            'entryId' => $this->getTargetGalleryEntryId(),
        ];

        $this->assertTrue($this->_webApiCall($this->deleteServiceInfo, $requestData));
        $targetProduct = $this->getTargetSimpleProduct();
        $mediaGallery = $targetProduct->getData('media_gallery');
        $this->assertCount(0, $mediaGallery['images']);
    }

    /**
     * Test create() method if provided content is not base64 encoded
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testCreateThrowsExceptionIfProvidedContentIsNotBase64Encoded()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The image content must be valid base64 encoded data.');

        $encodedContent = 'not_a_base64_encoded_content';
        $requestData = [
            'id' => null,
            'media_type' => 'image',
            'label' => 'Image Text',
            'position' => 1,
            'types' => ['image'],
            'disabled' => false,
            'content' => [
                'base64_encoded_data' => $encodedContent,
                'type' => 'image/jpeg',
                'name' => 'test_image.jpg',
            ]
        ];

        $this->_webApiCall($this->createServiceInfo, ['sku' => 'simple', 'entry' => $requestData]);
    }

    /**
     * Test create() method if provided content is not an image
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testCreateThrowsExceptionIfProvidedContentIsNotAnImage()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The image content must be valid base64 encoded data.');

        $encodedContent = base64_encode('not_an_image');
        $requestData = [
            'id' => null,
            'media_type' => 'image',
            'label' => 'Image Text',
            'position' => 1,
            'types' => ['image'],
            'disabled' => false,
            'content' => [
                'base64_encoded_data' => $encodedContent,
                'type' => 'image/jpeg',
                'name' => 'test_image.jpg',
            ]
        ];

        $this->_webApiCall($this->createServiceInfo, ['sku' => 'simple', 'entry' => $requestData]);
    }

    /**
     * Test create() method if provided image has wrong MIME type
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testCreateThrowsExceptionIfProvidedImageHasWrongMimeType()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The image MIME type is not valid or not supported.');

        $encodedContent = base64_encode(file_get_contents($this->testImagePath));
        $requestData = [
            'id' => null,
            'media_type' => 'image',
            'label' => 'Image Text',
            'position' => 1,
            'types' => ['image'],
            'disabled' => false,
            'content' => [
                'base64_encoded_data' => $encodedContent,
                'type' => 'wrong_mime_type',
                'name' => 'test_image.jpg',
            ]
        ];

        $this->_webApiCall($this->createServiceInfo, ['sku' => 'simple', 'entry' => $requestData]);
    }

    /**
     * Test create method if target product does not exist
     *
     */
    public function testCreateThrowsExceptionIfTargetProductDoesNotExist()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The product that was requested doesn\'t exist. Verify the product and try again.');

        $this->createServiceInfo['rest']['resourcePath'] = '/V1/products/wrong_product_sku/media';

        $requestData = [
            'id' => null,
            'media_type' => 'image',
            'label' => 'Image Text',
            'position' => 1,
            'types' => ['image'],
            'disabled' => false,
            'content' => [
                'base64_encoded_data' => base64_encode(file_get_contents($this->testImagePath)),
                'type' => 'image/jpeg',
                'name' => 'test_image.jpg',
            ]
        ];

        $this->_webApiCall($this->createServiceInfo, ['sku' => 'simple', 'entry' => $requestData]);
    }

    /**
     * Test create() method if provided image name contains forbidden characters
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testCreateThrowsExceptionIfProvidedImageNameContainsForbiddenCharacters()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Provided image name contains forbidden characters.');

        $requestData = [
            'id' => null,
            'media_type' => 'image',
            'label' => 'Image Text',
            'position' => 1,
            'types' => ['image'],
            'disabled' => false,
            'content' => [
                'base64_encoded_data' => base64_encode(file_get_contents($this->testImagePath)),
                'type' => 'image/jpeg',
                'name' => 'test/\\{}|:"<>', // Cannot contain \ / : * ? " < > |
            ]
        ];

        $this->_webApiCall($this->createServiceInfo, ['sku' => 'simple', 'entry' => $requestData]);
    }

    /**
     * Test update() method if target product does not exist
     *
     */
    public function testUpdateThrowsExceptionIfTargetProductDoesNotExist()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The product that was requested doesn\'t exist. Verify the product and try again.');

        $this->updateServiceInfo['rest']['resourcePath'] = '/V1/products/wrong_product_sku/media'
            . '/' . 'wrong-sku';
        $requestData = [
            'sku' => 'wrong_product_sku',
            'entry' => [
                'id' => 9999,
                'media_type' => 'image',
                'label' => 'Updated Image Text',
                'position' => 1,
                'types' => ['thumbnail'],
                'disabled' => true,
            ],
        ];

        $this->_webApiCall($this->updateServiceInfo, $requestData, null, 'all');
    }

    /**
     * Test update() method if there is no image with given id
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_image.php
     */
    public function testUpdateThrowsExceptionIfThereIsNoImageWithGivenId()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No image with the provided ID was found. Verify the ID and try again.');

        $requestData = [
            'sku' => 'simple',
            'entry' => [
                'id' => 9999,
                'media_type' => 'image',
                'label' => 'Updated Image Text',
                'position' => 1,
                'types' => ['thumbnail'],
                'disabled' => true,
            ],
        ];

        $this->updateServiceInfo['rest']['resourcePath'] = $this->updateServiceInfo['rest']['resourcePath']
            . '/' . $this->getTargetGalleryEntryId();

        $this->_webApiCall($this->updateServiceInfo, $requestData, null, 'all');
    }

    /**
     * Test delete() method if target product does not exist
     *
     */
    public function testDeleteThrowsExceptionIfTargetProductDoesNotExist()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The product that was requested doesn\'t exist. Verify the product and try again.');

        $this->deleteServiceInfo['rest']['resourcePath'] = '/V1/products/wrong_product_sku/media/9999';
        $requestData = [
            'sku' => 'wrong_product_sku',
            'entryId' => 9999,
        ];

        $this->_webApiCall($this->deleteServiceInfo, $requestData);
    }

    /**
     * Test delete() method if there is no image with given id
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_image.php
     */
    public function testDeleteThrowsExceptionIfThereIsNoImageWithGivenId()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No image with the provided ID was found. Verify the ID and try again.');

        $this->deleteServiceInfo['rest']['resourcePath'] = '/V1/products/simple/media/9999';
        $requestData = [
            'sku' => 'simple',
            'entryId' => 9999,
        ];

        $this->_webApiCall($this->deleteServiceInfo, $requestData);
    }

    /**
     * Test get() method
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_image.php
     */
    public function testGet()
    {
        $productSku = 'simple';

        /** @var ProductRepository $repository */
        $repository = $this->objectManager->create(ProductRepository::class);
        $product = $repository->get($productSku);
        $image = current($product->getMediaGallery('images'));
        $imageId = $image['value_id'];

        $expected = [
            'label' => $image['label'],
            'media_type' => $image['media_type'],
            'position' => $image['position'],
            'disabled' => (bool)$image['disabled'],
            'file' => $image['file'],
            'types' => ['image', 'small_image', 'thumbnail'],
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/' . $productSku . '/media/' . $imageId,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => 'catalogProductAttributeMediaGalleryManagementV1',
                'serviceVersion' => 'V1',
                'operation' => 'catalogProductAttributeMediaGalleryManagementV1Get',
            ],
        ];
        $requestData = [
            'sku' => $productSku,
            'entryId' => $imageId,
        ];
        $data = $this->_webApiCall($serviceInfo, $requestData);
        $actual = (array)$data;
        $this->assertEquals($expected['label'], $actual['label']);
        $this->assertEquals($expected['position'], $actual['position']);
        $this->assertEquals($expected['file'], $actual['file']);
        $this->assertEquals($expected['types'], $actual['types']);
        $this->assertEquals($expected['media_type'], $actual['media_type']);
        $this->assertEquals($expected['disabled'], (bool)$actual['disabled']);
    }

    /**
     * Test getList() method
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_image.php
     */
    public function testGetList()
    {
        $productSku = 'simple'; //from fixture
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/' . urlencode($productSku) . '/media',
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => 'catalogProductAttributeMediaGalleryManagementV1',
                'serviceVersion' => 'V1',
                'operation' => 'catalogProductAttributeMediaGalleryManagementV1GetList',
            ],
        ];

        $requestData = [
            'sku' => $productSku,
        ];
        $imageList = $this->_webApiCall($serviceInfo, $requestData);

        $image = reset($imageList);
        $this->assertEquals('/m/a/magento_image.jpg', $image['file']);
        $this->assertNotEmpty($image['types']);
        $imageTypes = $image['types'];
        $this->assertContains('image', $imageTypes);
        $this->assertContains('small_image', $imageTypes);
        $this->assertContains('thumbnail', $imageTypes);
    }

    /**
     * Test getList() method for absent sku
     */
    public function testGetListForAbsentSku()
    {
        $productSku = 'absent_sku_' . time();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/' . urlencode($productSku) . '/media',
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => 'catalogProductAttributeMediaGalleryManagementV1',
                'serviceVersion' => 'V1',
                'operation' => 'catalogProductAttributeMediaGalleryManagementV1GetList',
            ],
        ];

        $requestData = [
            'sku' => $productSku,
        ];
        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
            $this->expectException('SoapFault');
            $this->expectExceptionMessage(
                "The product that was requested doesn't exist. Verify the product and try again."
            );
        } else {
            $this->expectException('Exception');
            $this->expectExceptionCode(404);
        }
        $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * Test addProductVideo() method
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testAddProductVideo()
    {
        $videoContent = [
            'media_type' => 'external-video',
            'video_provider' => 'vimeo',
            'video_url' => 'https://vimeo.com/testUrl',
            'video_title' => 'Vimeo Test Title',
            'video_description' => 'test description',
            'video_metadata' => 'video meta data'
        ];

        $requestData = [
            'id' => null,
            'media_type' => 'external-video',
            'label' => 'Image Text',
            'position' => 1,
            'types' => null,
            'disabled' => false,
            'content' => [
                ImageContentInterface::BASE64_ENCODED_DATA => base64_encode(file_get_contents($this->testImagePath)),
                ImageContentInterface::TYPE => 'image/jpeg',
                ImageContentInterface::NAME => 'test_image.jpg'
            ],
            'extension_attributes' => [
                'video_content' => $videoContent
            ]
        ];

        $actualResult = $this->_webApiCall($this->createServiceInfo, ['sku' => 'simple', 'entry' => $requestData]);
        $targetProduct = $this->getTargetSimpleProduct();
        $mediaGallery = $targetProduct->getData('media_gallery');

        $this->assertCount(1, $mediaGallery['images']);
        $updatedImage = array_shift($mediaGallery['images']);
        $this->assertEquals($actualResult, $updatedImage['value_id']);
        $this->assertEquals('Image Text', $updatedImage['label']);
        $this->assertEquals(1, $updatedImage['position']);
        $this->assertEquals(0, $updatedImage['disabled']);
        $this->assertStringStartsWith('/t/e/test_image', $updatedImage['file']);
        $this->assertEquals($videoContent, array_intersect($updatedImage, $videoContent));
    }
}
