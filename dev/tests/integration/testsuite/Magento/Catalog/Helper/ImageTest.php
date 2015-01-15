<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;

class ImageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_helper;

    /**
     * @var string
     */
    protected static $_sampleCachedUrl = '';

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected static $_product;

    public static function setUpBeforeClass()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $config = $objectManager->get('Magento\Catalog\Model\Product\Media\Config');
        /** @var \Magento\Framework\Filesystem\Directory\WriteInterface $mediaDirectory */
        $mediaDirectory = $objectManager->get(
            'Magento\Framework\Filesystem'
        )->getDirectoryWrite(
            DirectoryList::MEDIA
        );

        // image fixtures
        $fixtureMediaDir = $mediaDirectory->getAbsolutePath($config->getBaseMediaPath());

        mkdir($fixtureMediaDir . '/m/a', 0777, true);
        $fixtureDir = dirname(__DIR__) . '/_files';
        copy("{$fixtureDir}/magento_image.jpg", $fixtureMediaDir . '/m/a/magento_image.jpg');
        copy("{$fixtureDir}/magento_small_image.jpg", $fixtureMediaDir . '/m/a/magento_small_image.jpg');
        copy("{$fixtureDir}/magento_thumbnail.jpg", $fixtureMediaDir . '/m/a/magento_thumbnail.jpg');

        // watermark fixture
        mkdir(
            $fixtureMediaDir . '/watermark/stores/' . $objectManager->get(
                'Magento\Store\Model\StoreManagerInterface'
            )->getStore()->getId(),
            0777,
            true
        );
        copy(
            "{$fixtureDir}/watermark.jpg",
            $fixtureMediaDir . '/watermark/stores/' . $objectManager->get(
                'Magento\Store\Model\StoreManagerInterface'
            )->getStore()->getId() . '/watermark.jpg'
        );

        // sample product with images
        self::$_product = $objectManager->create('Magento\Catalog\Model\Product');
        self::$_product->addData(
            [
                'image' => '/m/a/magento_image.jpg',
                'small_image' => '/m/a/magento_small_image.jpg',
                'thumbnail' => '/m/a/magento_thumbnail.jpg',
            ]
        );

        // sample image cached URL
        $helper = $objectManager->get('Magento\Catalog\Helper\Image');
        self::$_sampleCachedUrl = (string)$helper->init(self::$_product, 'image');
    }

    public static function tearDownAfterClass()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $config = $objectManager->get('Magento\Catalog\Model\Product\Media\Config');
        /** @var \Magento\Framework\Filesystem\Directory\WriteInterface $mediaDirectory */
        $mediaDirectory = $objectManager->get(
            'Magento\Framework\Filesystem'
        )->getDirectoryWrite(
            DirectoryList::MEDIA
        );

        $mediaDirectory->delete($config->getBaseMediaPath());
        $mediaDirectory->delete($config->getBaseTmpMediaPath());
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->_helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Catalog\Helper\Image'
        );
    }

    /**
     * init()
     * __toString()
     * @dataProvider initDataProvider
     */
    public function testInit($imageType, $expectedEnding)
    {
        $this->assertStringEndsWith($expectedEnding, (string)$this->_init($imageType));
    }

    public function initDataProvider()
    {
        return [
            ['image', '/m/a/magento_image.jpg'],
            ['small_image', '/m/a/magento_small_image.jpg'],
            ['thumbnail', '/m/a/magento_thumbnail.jpg']
        ];
    }

    public function testResize()
    {
        $this->assertNotEquals((string)$this->_init()->resize(100), self::$_sampleCachedUrl);
    }

    public function testSetQuality()
    {
        $this->assertNotEquals((string)$this->_init()->setQuality(50), self::$_sampleCachedUrl);
    }

    public function testKeepAspectRatio()
    {
        $this->assertEquals((string)$this->_init()->keepAspectRatio(true), self::$_sampleCachedUrl);
        $this->assertNotEquals((string)$this->_init()->keepAspectRatio(false), self::$_sampleCachedUrl);
    }

    public function testKeepFrame()
    {
        $this->assertEquals((string)$this->_init()->keepFrame(true), self::$_sampleCachedUrl);
        $this->assertNotEquals((string)$this->_init()->keepFrame(false), self::$_sampleCachedUrl);
    }

    public function testKeepTransparency()
    {
        $this->assertEquals((string)$this->_init()->keepTransparency(true), self::$_sampleCachedUrl);
        $this->assertNotEquals((string)$this->_init()->keepTransparency(false), self::$_sampleCachedUrl);
    }

    public function testConstrainOnly()
    {
        $this->assertEquals((string)$this->_init()->constrainOnly(false), self::$_sampleCachedUrl);
        $this->assertNotEquals((string)$this->_init()->constrainOnly(true), self::$_sampleCachedUrl);
    }

    public function testBackgroundColor()
    {
        $rgbArray = (string)$this->_init()->backgroundColor([100, 100, 100]);
        $rgbArgs = (string)$this->_init()->backgroundColor(100, 100, 100);
        $this->assertEquals($rgbArgs, $rgbArray);
        $this->assertNotEquals($rgbArray, self::$_sampleCachedUrl);
        $this->assertNotEquals($rgbArgs, self::$_sampleCachedUrl);
    }

    public function testRotate()
    {
        $this->assertNotEquals((string)$this->_init()->rotate(15), self::$_sampleCachedUrl);
    }

    public function testWatermark()
    {
        $this->assertNotEquals(
            (string)$this->_init()->watermark('/watermark.jpg', 0, '15x15', 50),
            self::$_sampleCachedUrl
        );
    }

    /**
     * placeholder()
     * getPlaceholder()
     *
     * @magentoAppIsolation enabled
     */
    public function testPlaceholder()
    {
        $this->_init();
        $defaultPlaceholder = $this->_helper->getPlaceholder();
        $this->assertNotEmpty($defaultPlaceholder);

        $placeholder = uniqid() . 'placeholder.png';
        $this->_helper->placeholder($placeholder);
        $this->assertEquals($placeholder, $this->_helper->getPlaceholder());

        $this->assertNotEquals($placeholder, $defaultPlaceholder);
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetPlaceholder()
    {
        /** @var $model \Magento\Catalog\Model\Product */
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
        $this->_helper->init($model, 'image');
        $placeholder = $this->_helper->getPlaceholder();
        $this->assertEquals('Magento_Catalog::images/product/placeholder/image.jpg', $placeholder);

        // test that placeholder doesn't change, once initialized
        $model->setDestinationSubDir('other_image');
        $this->assertEquals($placeholder, $this->_helper->getPlaceholder());
    }

    /**
     * getOriginalWidth()
     * getOriginalHeight()
     * getOriginalSizeArray()
     */
    public function testGetOriginalWidthHeight()
    {
        $this->assertEquals(272, $this->_init()->getOriginalWidth());
        $this->assertEquals(261, $this->_init()->getOriginalHeight());
        $this->assertEquals([272, 261], $this->_init()->getOriginalSizeArray());
    }

    /**
     * Initialize image by specified type
     *
     * @param string $imageType
     * @return \Magento\Catalog\Helper\Image
     */
    protected function _init($imageType = 'image')
    {
        return $this->_helper->init(self::$_product, $imageType);
    }
}
