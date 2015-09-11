<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Attribute\Backend;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Test class for \Magento\Catalog\Model\Product\Attribute\Backend\Media.
 * @magentoDataFixture Magento/Catalog/_files/product_simple.php
 */
class MediaTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Backend\Media
     */
    protected $_model;

    /**
     * @var string
     */
    protected static $_mediaTmpDir;

    /**
     * @var string
     */
    protected static $_mediaDir;

    public static function setUpBeforeClass()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Framework\Filesystem\Directory\WriteInterface $mediaDirectory */
        $config = $objectManager->get('Magento\Catalog\Model\Product\Media\Config');
        $mediaDirectory = $objectManager->get(
            'Magento\Framework\Filesystem'
        )->getDirectoryWrite(
            DirectoryList::MEDIA
        );

        self::$_mediaTmpDir = $mediaDirectory->getAbsolutePath($config->getBaseTmpMediaPath());
        self::$_mediaDir = $mediaDirectory->getAbsolutePath($config->getBaseMediaPath());
        $fixtureDir = realpath(__DIR__ . '/../../../../_files');

        $mediaDirectory->create($config->getBaseTmpMediaPath());
        $mediaDirectory->create($config->getBaseMediaPath());

        copy($fixtureDir . "/magento_image.jpg", self::$_mediaTmpDir . "/magento_image.jpg");
        copy($fixtureDir . "/magento_image.jpg", self::$_mediaDir . "/magento_image.jpg");
        copy($fixtureDir . "/magento_small_image.jpg", self::$_mediaTmpDir . "/magento_small_image.jpg");
    }

    public static function tearDownAfterClass()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Catalog\Model\Product\Media\Config $config */
        $config = $objectManager->get('Magento\Catalog\Model\Product\Media\Config');

        /** @var \Magento\Framework\Filesystem\Directory\WriteInterface $mediaDirectory */
        $mediaDirectory = $objectManager->get(
            'Magento\Framework\Filesystem'
        )->getDirectoryWrite(
            DirectoryList::MEDIA
        );

        if ($mediaDirectory->isExist($config->getBaseMediaPath())) {
            $mediaDirectory->delete($config->getBaseMediaPath());
        }
        if ($mediaDirectory->isExist($config->getBaseTmpMediaPath())) {
            $mediaDirectory->delete($config->getBaseTmpMediaPath());
        }
    }

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product\Attribute\Backend\Media'
        );
        $this->_model->setAttribute(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\Eav\Model\Config'
            )->getAttribute(
                'catalog_product',
                'media_gallery'
            )
        );
    }

    public function testAfterLoad()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $this->_model->afterLoad($product);
        $data = $product->getData();
        $this->assertArrayHasKey('media_gallery', $data);
        $this->assertArrayHasKey('images', $data['media_gallery']);
        $this->assertArrayHasKey('values', $data['media_gallery']);
    }

    public function testValidate()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $this->assertTrue($this->_model->validate($product));
        $this->_model->getAttribute()->setIsRequired(true);
        try {
            $this->assertFalse($this->_model->validate($product));
            $this->_model->getAttribute()->setIsRequired(false);
        } catch (\Exception $e) {
            $this->_model->getAttribute()->setIsRequired(false);
            throw $e;
        }
    }

    /**
     * @covers \Magento\Catalog\Model\Product\Attribute\Backend\Media::beforeSave
     * @covers \Magento\Catalog\Model\Product\Attribute\Backend\Media::getRenamedImage
     */
    public function testBeforeSave()
    {
        $fileName = 'magento_image.jpg';
        $fileLabel = 'Magento image';
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->setData(
            'media_gallery',
            ['images' => ['image' => ['file' => $fileName, 'label' => $fileLabel]]]
        );
        $product->setData('image', $fileName);
        $this->_model->beforeSave($product);
        $this->assertStringStartsWith('./magento_image', $product->getData('media_gallery/images/image/new_file'));
        $this->assertEquals($fileLabel, $product->getData('image_label'));

        $product->setIsDuplicate(true);
        $product->setData(
            'media_gallery',
            ['images' => ['image' => ['value_id' => '100', 'file' => $fileName, 'label' => $fileLabel]]]
        );
        $this->_model->beforeSave($product);
        $this->assertStringStartsWith('./magento_image', $product->getData('media_gallery/duplicate/100'));
        $this->assertEquals($fileLabel, $product->getData('image_label'));
    }

    public function testAfterSaveAndAfterLoad()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->setId(1);
        $product->setData('media_gallery', ['images' => ['image' => ['file' => 'magento_image.jpg']]]);
        $this->_model->afterSave($product);

        $this->assertEmpty($product->getData('media_gallery/images/0/value_id'));
        $this->_model->afterLoad($product);
        $this->assertNotEmpty($product->getData('media_gallery/images/0/value_id'));
    }

    public function testAddImage()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->setId(1);
        $file = $this->_model->addImage($product, self::$_mediaTmpDir . '/magento_small_image.jpg');
        $this->assertStringMatchesFormat('/m/a/magento_small_image%sjpg', $file);
    }

    public function testUpdateImage()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->setData('media_gallery', ['images' => ['image' => ['file' => 'magento_image.jpg']]]);
        $this->_model->updateImage($product, 'magento_image.jpg', ['label' => 'test label']);
        $this->assertEquals('test label', $product->getData('media_gallery/images/image/label'));
    }

    public function testRemoveImage()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->setData('media_gallery', ['images' => ['image' => ['file' => 'magento_image.jpg']]]);
        $this->_model->removeImage($product, 'magento_image.jpg');
        $this->assertEquals('1', $product->getData('media_gallery/images/image/removed'));
    }

    public function testGetImage()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->setData('media_gallery', ['images' => ['image' => ['file' => 'magento_image.jpg']]]);

        $this->assertEquals(
            ['file' => 'magento_image.jpg'],
            $this->_model->getImage($product, 'magento_image.jpg')
        );
    }

    public function testClearMediaAttribute()
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->setData(['test_media1' => 'test1', 'test_media2' => 'test2', 'test_media3' => 'test3']);
        $product->setMediaAttributes(['test_media1', 'test_media2', 'test_media3']);

        $this->assertNotEmpty($product->getData('test_media1'));
        $this->_model->clearMediaAttribute($product, 'test_media1');
        $this->assertNull($product->getData('test_media1'));

        $this->assertNotEmpty($product->getData('test_media2'));
        $this->assertNotEmpty($product->getData('test_media3'));
        $this->_model->clearMediaAttribute($product, ['test_media2', 'test_media3']);
        $this->assertNull($product->getData('test_media2'));
        $this->assertNull($product->getData('test_media3'));
    }

    public function testSetMediaAttribute()
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->setMediaAttributes(['test_media1', 'test_media2', 'test_media3']);
        $this->_model->setMediaAttribute($product, 'test_media1', 'test1');
        $this->assertEquals('test1', $product->getData('test_media1'));

        $this->_model->setMediaAttribute($product, ['test_media2', 'test_media3'], 'test');
        $this->assertEquals('test', $product->getData('test_media2'));
        $this->assertEquals('test', $product->getData('test_media3'));
    }
}
