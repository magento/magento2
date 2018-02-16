<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

use Magento\Framework\Api\Data\ImageContentInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class ProductAttributeMediaGalleryManagementInterfaceTest
 */
class ProductAttributeMediaGalleryManagementInterfaceTest extends \Magento\TestFramework\TestCase\WebapiAbstract
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

    protected function setUp()
    {
        $this->createServiceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/simple/media',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
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
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => 'catalogProductAttributeMediaGalleryManagementV1',
                'serviceVersion' => 'V1',
                'operation' => 'catalogProductAttributeMediaGalleryManagementV1Update',
            ],
        ];
        $this->deleteServiceInfo = [
            'rest' => [
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
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
        $objectManager = Bootstrap::getObjectManager();
        return $objectManager->get('Magento\Catalog\Model\ProductFactory')->create()->load(1);
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
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testCreate()
    {
        $requestData = [
            'id' => null,
            'media_type' => \Magento\Catalog\Model\Product\Attribute\Backend\Media\ImageEntryConverter::MEDIA_TYPE_CODE,
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
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testCreateWithoutFileExtension()
    {
        $requestData = [
            'id' => null,
            'media_type' => \Magento\Catalog\Model\Product\Attribute\Backend\Media\ImageEntryConverter::MEDIA_TYPE_CODE,
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
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testCreateWithNotDefaultStoreId()
    {
        $requestData = [
            'id' => null,
            'media_type' => \Magento\Catalog\Model\Product\Attribute\Backend\Media\ImageEntryConverter::MEDIA_TYPE_CODE,
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
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_image.php
     */
    public function testUpdate()
    {
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

        $targetProduct = $this->getTargetSimpleProduct();
        $this->assertEquals('/m/a/magento_image.jpg', $targetProduct->getData('thumbnail'));
        $this->assertNull($targetProduct->getData('image'));
        $this->assertNull($targetProduct->getData('small_image'));
        $mediaGallery = $targetProduct->getData('media_gallery');
        $this->assertCount(1, $mediaGallery['images']);
        $updatedImage = array_shift($mediaGallery['images']);
        $this->assertEquals('Updated Image Text', $updatedImage['label']);
        $this->assertEquals('/m/a/magento_image.jpg', $updatedImage['file']);
        $this->assertEquals(10, $updatedImage['position']);
        $this->assertEquals(1, $updatedImage['disabled']);
        $this->assertEquals('Updated Image Text', $updatedImage['label_default']);
        $this->assertEquals(10, $updatedImage['position_default']);
        $this->assertEquals(1, $updatedImage['disabled_default']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_image.php
     */
    public function testUpdateWithNotDefaultStoreId()
    {
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

        $targetProduct = $this->getTargetSimpleProduct();
        $this->assertEquals('/m/a/magento_image.jpg', $targetProduct->getData('thumbnail'));
        $mediaGallery = $targetProduct->getData('media_gallery');
        $this->assertCount(1, $mediaGallery['images']);
        $updatedImage = array_shift($mediaGallery['images']);
        // Not default store view values were updated
        $this->assertEquals('Updated Image Text', $updatedImage['label']);
        $this->assertEquals('/m/a/magento_image.jpg', $updatedImage['file']);
        $this->assertEquals(10, $updatedImage['position']);
        $this->assertEquals(1, $updatedImage['disabled']);
        // Default store view values were not updated
        $this->assertEquals('Image Alt Text', $updatedImage['label_default']);
        $this->assertEquals(1, $updatedImage['position_default']);
        $this->assertEquals(0, $updatedImage['disabled_default']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_image.php
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
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @expectedException \Exception
     * @expectedExceptionMessage The image content must be valid base64 encoded data.
     */
    public function testCreateThrowsExceptionIfProvidedContentIsNotBase64Encoded()
    {
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
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @expectedException \Exception
     * @expectedExceptionMessage The image content must be valid base64 encoded data.
     */
    public function testCreateThrowsExceptionIfProvidedContentIsNotAnImage()
    {
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
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @expectedException \Exception
     * @expectedExceptionMessage The image MIME type is not valid or not supported.
     */
    public function testCreateThrowsExceptionIfProvidedImageHasWrongMimeType()
    {
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
     * @expectedException \Exception
     * @expectedExceptionMessage Requested product doesn't exist
     */
    public function testCreateThrowsExceptionIfTargetProductDoesNotExist()
    {
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
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @expectedException \Exception
     * @expectedExceptionMessage Provided image name contains forbidden characters.
     */
    public function testCreateThrowsExceptionIfProvidedImageNameContainsForbiddenCharacters()
    {
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
     * @expectedException \Exception
     * @expectedExceptionMessage Requested product doesn't exist
     */
    public function testUpdateThrowsExceptionIfTargetProductDoesNotExist()
    {
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
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_image.php
     * @expectedException \Exception
     * @expectedExceptionMessage There is no image with provided ID.
     */
    public function testUpdateThrowsExceptionIfThereIsNoImageWithGivenId()
    {
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
     * @expectedException \Exception
     * @expectedExceptionMessage Requested product doesn't exist
     */
    public function testDeleteThrowsExceptionIfTargetProductDoesNotExist()
    {
        $this->deleteServiceInfo['rest']['resourcePath'] = '/V1/products/wrong_product_sku/media/9999';
        $requestData = [
            'sku' => 'wrong_product_sku',
            'entryId' => 9999,
        ];

        $this->_webApiCall($this->deleteServiceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_image.php
     * @expectedException \Exception
     * @expectedExceptionMessage There is no image with provided ID.
     */
    public function testDeleteThrowsExceptionIfThereIsNoImageWithGivenId()
    {
        $this->deleteServiceInfo['rest']['resourcePath'] = '/V1/products/simple/media/9999';
        $requestData = [
            'sku' => 'simple',
            'entryId' => 9999,
        ];

        $this->_webApiCall($this->deleteServiceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_image.php
     */
    public function testGet()
    {
        $productSku = 'simple';

        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        /** @var \Magento\Catalog\Model\ProductRepository $repository */
        $repository = $objectManager->create('Magento\Catalog\Model\ProductRepository');
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
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
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
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_image.php
     */
    public function testGetList()
    {
        $productSku = 'simple'; //from fixture
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/' . urlencode($productSku) . '/media',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
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

    public function testGetListForAbsentSku()
    {
        $productSku = 'absent_sku_' . time();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/' . urlencode($productSku) . '/media',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
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
            $this->setExpectedException('SoapFault', 'Requested product doesn\'t exist');
        } else {
            $this->setExpectedException('Exception', '', 404);
        }
        $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
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
