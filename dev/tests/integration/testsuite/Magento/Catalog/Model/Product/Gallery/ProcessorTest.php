<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Gallery;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Test class for \Magento\Catalog\Model\Product\Gallery\Processor.
 * @magentoDataFixture Magento/Catalog/_files/product_simple.php
 */
class ProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Gallery\Processor
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
        $config = $objectManager->get(\Magento\Catalog\Model\Product\Media\Config::class);
        $mediaDirectory = $objectManager->get(
            \Magento\Framework\Filesystem::class
        )->getDirectoryWrite(
            DirectoryList::MEDIA
        );

        self::$_mediaTmpDir = $mediaDirectory->getAbsolutePath($config->getBaseTmpMediaPath());
        self::$_mediaDir = $mediaDirectory->getAbsolutePath($config->getBaseMediaPath());
        $fixtureDir = realpath(__DIR__ . '/../../../_files');

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
        $config = $objectManager->get(\Magento\Catalog\Model\Product\Media\Config::class);

        /** @var \Magento\Framework\Filesystem\Directory\WriteInterface $mediaDirectory */
        $mediaDirectory = $objectManager->get(
            \Magento\Framework\Filesystem::class
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
            \Magento\Catalog\Model\Product\Gallery\Processor::class
        );
    }

    public function testValidate()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
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

    public function testAddImage()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );
        $product->setId(1);
        $file = $this->_model->addImage($product, self::$_mediaTmpDir . '/magento_small_image.jpg');
        $this->assertStringMatchesFormat('/m/a/magento_small_image%sjpg', $file);
    }

    public function testUpdateImage()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );
        $product->setData('media_gallery', ['images' => ['image' => ['file' => 'magento_image.jpg']]]);
        $this->_model->updateImage($product, 'magento_image.jpg', ['label' => 'test label']);
        $this->assertEquals('test label', $product->getData('media_gallery/images/image/label'));
    }

    public function testRemoveImage()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );
        $product->setData('media_gallery', ['images' => ['image' => ['file' => 'magento_image.jpg']]]);
        $this->_model->removeImage($product, 'magento_image.jpg');
        $this->assertEquals('1', $product->getData('media_gallery/images/image/removed'));
    }

    public function testGetImage()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
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
            \Magento\Catalog\Model\Product::class
        );
        $product->setData(['image' => 'test1', 'small_image' => 'test2', 'thumbnail' => 'test3']);

        $this->assertNotEquals('no_selection', $product->getData('image'));
        $this->_model->clearMediaAttribute($product, 'image');
        $this->assertEquals('no_selection', $product->getData('image'));

        $this->assertNotEquals('no_selection', $product->getData('small_image'));
        $this->assertNotEquals('no_selection', $product->getData('thumbnail'));
        $this->_model->clearMediaAttribute($product, ['small_image', 'thumbnail']);
        $this->assertEquals('no_selection', $product->getData('small_image'));
        $this->assertEquals('no_selection', $product->getData('thumbnail'));
    }

    public function testSetMediaAttribute()
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );
        $this->_model->setMediaAttribute($product, 'image', 'test1');
        $this->assertEquals('test1', $product->getData('image'));

        $this->_model->setMediaAttribute($product, ['non-exist-image-attribute', 'small_image'], 'test');
        $this->assertNull($product->getData('non-exist-image-attribute'));
        $this->assertEquals('test', $product->getData('small_image'));
    }
}
