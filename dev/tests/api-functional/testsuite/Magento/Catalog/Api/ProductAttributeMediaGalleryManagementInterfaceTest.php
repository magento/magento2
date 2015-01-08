<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Api;

use Magento\Webapi\Model\Rest\Config as RestConfig;
use Magento\TestFramework\Helper\Bootstrap;

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
                'httpMethod' => RestConfig::HTTP_METHOD_POST,
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
                'httpMethod' => RestConfig::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => 'catalogProductAttributeMediaGalleryManagementV1',
                'serviceVersion' => 'V1',
                'operation' => 'catalogProductAttributeMediaGalleryManagementV1Update',
            ],
        ];
        $this->deleteServiceInfo = [
            'rest' => [
                'httpMethod' => RestConfig::HTTP_METHOD_DELETE,
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
        return (int)$mediaGallery['images'][0]['value_id'];
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testCreate()
    {
        $requestData = [
            'productSku' => 'simple',
            'entry' => [
                'id' => null,
                'label' => 'Image Text',
                'position' => 1,
                'types' => ['image'],
                'is_disabled' => false,
            ],
            'entryContent' => [
                'entry_data' => base64_encode(file_get_contents($this->testImagePath)),
                'mime_type' => 'image/jpeg',
                'name' => 'test_image',
            ],
            // Store ID is not provided so the default one must be used
        ];

        $actualResult = $this->_webApiCall($this->createServiceInfo, $requestData);
        $targetProduct = $this->getTargetSimpleProduct();
        $mediaGallery = $targetProduct->getData('media_gallery');

        $this->assertCount(1, $mediaGallery['images']);
        $updatedImage = $mediaGallery['images'][0];
        $this->assertEquals($actualResult, $updatedImage['value_id']);
        $this->assertEquals('Image Text', $updatedImage['label']);
        $this->assertEquals(1, $updatedImage['position']);
        $this->assertEquals(0, $updatedImage['disabled']);
        $this->assertEquals('Image Text', $updatedImage['label_default']);
        $this->assertEquals(1, $updatedImage['position_default']);
        $this->assertEquals(0, $updatedImage['disabled_default']);
        $this->assertStringStartsWith('/t/e/test_image', $updatedImage['file']);
        $this->assertEquals($updatedImage['file'], $targetProduct->getData('image'));
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testCreateWithNotDefaultStoreId()
    {
        $requestData = [
            'productSku' => 'simple',
            'entry' => [
                'id' => null,
                'label' => 'Image Text',
                'position' => 1,
                'types' => ['image'],
                'is_disabled' => false,
            ],
            'entryContent' => [
                'entry_data' => base64_encode(file_get_contents($this->testImagePath)),
                'mime_type' => 'image/jpeg',
                'name' => 'test_image',
            ],
            'storeId' => 1,
        ];

        $actualResult = $this->_webApiCall($this->createServiceInfo, $requestData);
        $targetProduct = $this->getTargetSimpleProduct();
        $mediaGallery = $targetProduct->getData('media_gallery');
        $this->assertCount(1, $mediaGallery['images']);
        $updatedImage = $mediaGallery['images'][0];
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
            'productSku' => 'simple',
            'entry' => [
                'id' => $this->getTargetGalleryEntryId(),
                'label' => 'Updated Image Text',
                'position' => 10,
                'types' => ['thumbnail'],
                'is_disabled' => true,
            ],
            // Store ID is not provided so the default one must be used
        ];

        $this->updateServiceInfo['rest']['resourcePath'] = $this->updateServiceInfo['rest']['resourcePath']
            . '/' . $this->getTargetGalleryEntryId();

        $this->assertTrue($this->_webApiCall($this->updateServiceInfo, $requestData));

        $targetProduct = $this->getTargetSimpleProduct();
        $this->assertEquals('/m/a/magento_image.jpg', $targetProduct->getData('thumbnail'));
        $this->assertNull($targetProduct->getData('image'));
        $this->assertNull($targetProduct->getData('small_image'));
        $mediaGallery = $targetProduct->getData('media_gallery');
        $this->assertCount(1, $mediaGallery['images']);
        $updatedImage = $mediaGallery['images'][0];
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
            'productSku' => 'simple',
            'entry' => [
                'id' => $this->getTargetGalleryEntryId(),
                'label' => 'Updated Image Text',
                'position' => 10,
                'types' => ['thumbnail'],
                'is_disabled' => true,
            ],
            'storeId' => 1,
        ];

        $this->updateServiceInfo['rest']['resourcePath'] = $this->updateServiceInfo['rest']['resourcePath']
            . '/' . $this->getTargetGalleryEntryId();

        $this->assertTrue($this->_webApiCall($this->updateServiceInfo, $requestData));

        $targetProduct = $this->getTargetSimpleProduct();
        $this->assertEquals('/m/a/magento_image.jpg', $targetProduct->getData('thumbnail'));
        $this->assertNull($targetProduct->getData('image'));
        $this->assertNull($targetProduct->getData('small_image'));
        $mediaGallery = $targetProduct->getData('media_gallery');
        $this->assertCount(1, $mediaGallery['images']);
        $updatedImage = $mediaGallery['images'][0];
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
            'productSku' => 'simple',
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
     * @expectedExceptionMessage There is no store with provided ID.
     */
    public function testCreateThrowsExceptionIfThereIsNoStoreWithProvidedStoreId()
    {
        $requestData = [
            'productSku' => 'simple',
            'entry' => [
                'id' => null,
                'label' => 'Image Text',
                'position' => 1,
                'types' => ['image'],
                'is_disabled' => false,
            ],
            'storeId' => 9999, // target store view does not exist
            'entryContent' => [
                'entry_data' => base64_encode(file_get_contents($this->testImagePath)),
                'mime_type' => 'image/jpeg',
                'name' => 'test_image',
            ],
        ];

        $this->_webApiCall($this->createServiceInfo, $requestData);
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
            'productSku' => 'simple',
            'entry' => [
                'id' => null,
                'label' => 'Image Text',
                'position' => 1,
                'is_disabled' => false,
                'types' => ['image'],
            ],
            'entryContent' => [
                'entry_data' => $encodedContent,
                'mime_type' => 'image/jpeg',
                'name' => 'test_image',
            ],
            'storeId' => 0,
        ];

        $this->_webApiCall($this->createServiceInfo, $requestData);
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
            'productSku' => 'simple',
            'entry' => [
                'id' => null,
                'is_disabled' => false,
                'label' => 'Image Text',
                'position' => 1,
                'types' => ['image'],
            ],
            'entryContent' => [
                'entry_data' => $encodedContent,
                'mime_type' => 'image/jpeg',
                'name' => 'test_image',
            ],
            'storeId' => 0,
        ];

        $this->_webApiCall($this->createServiceInfo, $requestData);
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
            'entry' => [
                'id' => null,
                'label' => 'Image Text',
                'position' => 1,
                'types' => ['image'],
                'is_disabled' => false,
            ],
            'productSku' => 'simple',
            'entryContent' => [
                'entry_data' => $encodedContent,
                'mime_type' => 'wrong_mime_type',
                'name' => 'test_image',
            ],
            'storeId' => 0,
        ];

        $this->_webApiCall($this->createServiceInfo, $requestData);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Requested product doesn't exist
     */
    public function testCreateThrowsExceptionIfTargetProductDoesNotExist()
    {
        $this->createServiceInfo['rest']['resourcePath'] = '/V1/products/wrong_product_sku/media';
        $requestData = [
            'productSku' => 'wrong_product_sku',
            'entry' => [
                'id' => null,
                'position' => 1,
                'label' => 'Image Text',
                'types' => ['image'],
                'is_disabled' => false,
            ],
            'entryContent' => [
                'entry_data' => base64_encode(file_get_contents($this->testImagePath)),
                'mime_type' => 'image/jpeg',
                'name' => 'test_image',
            ],
            'storeId' => 0,
        ];

        $this->_webApiCall($this->createServiceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @expectedException \Exception
     * @expectedExceptionMessage Provided image name contains forbidden characters.
     */
    public function testCreateThrowsExceptionIfProvidedImageNameContainsForbiddenCharacters()
    {
        $requestData = [
            'productSku' => 'simple',
            'entry' => [
                'id' => null,
                'label' => 'Image Text',
                'position' => 1,
                'types' => ['image'],
                'is_disabled' => false,
            ],
            'entryContent' => [
                'entry_data' => base64_encode(file_get_contents($this->testImagePath)),
                'mime_type' => 'image/jpeg',
                'name' => 'test/\\{}|:"<>', // Cannot contain \ / : * ? " < > |
            ],
            'storeId' => 0,
        ];

        $this->_webApiCall($this->createServiceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_image.php
     * @expectedException \Exception
     * @expectedExceptionMessage There is no store with provided ID.
     */
    public function testUpdateIfThereIsNoStoreWithProvidedStoreId()
    {
        $requestData = [
            'productSku' => 'simple',
            'entry' => [
                'id' => $this->getTargetGalleryEntryId(),
                'label' => 'Updated Image Text',
                'position' => 10,
                'types' => ['thumbnail'],
                'is_disabled' => true,
            ],
            'storeId' => 9999, // target store view does not exist
        ];

        $this->updateServiceInfo['rest']['resourcePath'] = $this->updateServiceInfo['rest']['resourcePath']
            . '/' . $this->getTargetGalleryEntryId();

        $this->_webApiCall($this->updateServiceInfo, $requestData);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Requested product doesn't exist
     */
    public function testUpdateThrowsExceptionIfTargetProductDoesNotExist()
    {
        $this->updateServiceInfo['rest']['resourcePath'] = '/V1/products/wrong_product_sku/media'
            . '/' . $this->getTargetGalleryEntryId();
        $requestData = [
            'productSku' => 'wrong_product_sku',
            'entry' => [
                'id' => 9999,
                'label' => 'Updated Image Text',
                'position' => 1,
                'types' => ['thumbnail'],
                'is_disabled' => true,
            ],
            'storeId' => 0,
        ];

        $this->_webApiCall($this->updateServiceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_image.php
     * @expectedException \Exception
     * @expectedExceptionMessage There is no image with provided ID.
     */
    public function testUpdateThrowsExceptionIfThereIsNoImageWithGivenId()
    {
        $requestData = [
            'productSku' => 'simple',
            'entry' => [
                'id' => 9999,
                'label' => 'Updated Image Text',
                'position' => 1,
                'types' => ['thumbnail'],
                'is_disabled' => true,
            ],
            'storeId' => 0,
        ];

        $this->updateServiceInfo['rest']['resourcePath'] = $this->updateServiceInfo['rest']['resourcePath']
            . '/' . $this->getTargetGalleryEntryId();

        $this->_webApiCall($this->updateServiceInfo, $requestData);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Requested product doesn't exist
     */
    public function testDeleteThrowsExceptionIfTargetProductDoesNotExist()
    {
        $this->deleteServiceInfo['rest']['resourcePath'] = '/V1/products/wrong_product_sku/media/9999';
        $requestData = [
            'productSku' => 'wrong_product_sku',
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
            'productSku' => 'simple',
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
            'position' => $image['position'],
            'is_disabled' => (bool)$image['disabled'],
            'file' => $image['file'],
            'types' => ['image', 'small_image', 'thumbnail'],
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/' . $productSku . '/media/' . $imageId,
                'httpMethod' => RestConfig::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => 'catalogProductAttributeMediaGalleryManagementV1',
                'serviceVersion' => 'V1',
                'operation' => 'catalogProductAttributeMediaGalleryManagementV1Get',
            ],
        ];
        $requestData = [
            'productSku' => $productSku,
            'imageId' => $imageId,
        ];
        $data = $this->_webApiCall($serviceInfo, $requestData);
        $actual = (array) $data;
        $this->assertEquals($expected['label'], $actual['label']);
        $this->assertEquals($expected['position'], $actual['position']);
        $this->assertEquals($expected['file'], $actual['file']);
        $this->assertEquals($expected['types'], $actual['types']);
        $this->assertEquals($expected['is_disabled'], (bool)$actual['is_disabled']);
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
                'httpMethod' => \Magento\Webapi\Model\Rest\Config::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => 'catalogProductAttributeMediaGalleryManagementV1',
                'serviceVersion' => 'V1',
                'operation' => 'catalogProductAttributeMediaGalleryManagementV1GetList',
            ],
        ];

        $requestData = [
            'productSku' => $productSku,
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
                'httpMethod' => \Magento\Webapi\Model\Rest\Config::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => 'catalogProductAttributeMediaGalleryManagementV1',
                'serviceVersion' => 'V1',
                'operation' => 'catalogProductAttributeMediaGalleryManagementV1GetList',
            ],
        ];

        $requestData = [
            'productSku' => $productSku,
        ];
        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
            $this->setExpectedException('SoapFault', 'Requested product doesn\'t exist');
        } else {
            $this->setExpectedException('Exception', '', 404);
        }
        $this->_webApiCall($serviceInfo, $requestData);
    }
}
