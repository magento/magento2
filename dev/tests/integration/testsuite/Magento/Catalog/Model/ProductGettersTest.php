<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Tests product model:
 * - general behaviour is tested (external interaction and pricing is not tested there)
 *
 * @see \Magento\Catalog\Model\ProductExternalTest
 * @see \Magento\Catalog\Model\ProductPriceTest
 * @magentoDataFixture Magento/Catalog/_files/categories.php
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductGettersTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_model;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    protected function setUp(): void
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );
        $this->productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            ProductRepositoryInterface::class
        );
    }

    public function testGetResourceCollection()
    {
        $collection = $this->_model->getResourceCollection();
        $this->assertInstanceOf(\Magento\Catalog\Model\ResourceModel\Product\Collection::class, $collection);
        $this->assertEquals($this->_model->getStoreId(), $collection->getStoreId());
    }

    public function testGetUrlModel()
    {
        $model = $this->_model->getUrlModel();
        $this->assertInstanceOf(\Magento\Catalog\Model\Product\Url::class, $model);
        $this->assertSame($model, $this->_model->getUrlModel());
    }

    public function testGetName()
    {
        $this->assertEmpty($this->_model->getName());
        $this->_model->setName('test');
        $this->assertEquals('test', $this->_model->getName());
    }

    public function testGetTypeId()
    {
        $this->assertEmpty($this->_model->getTypeId());
        $this->_model->setTypeId('simple');
        $this->assertEquals('simple', $this->_model->getTypeId());
    }

    public function testGetStatus()
    {
        $this->assertEquals(
            \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
            $this->_model->getStatus()
        );

        $this->_model->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);

        $this->assertEquals(
            \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED,
            $this->_model->getStatus()
        );
    }

    public function testGetSetTypeInstance()
    {
        // model getter
        $typeInstance = $this->_model->getTypeInstance();
        $this->assertInstanceOf(\Magento\Catalog\Model\Product\Type\AbstractType::class, $typeInstance);
        $this->assertSame($typeInstance, $this->_model->getTypeInstance());

        // singleton
        /** @var $otherProduct \Magento\Catalog\Model\Product */
        $otherProduct = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );
        $this->assertSame($typeInstance, $otherProduct->getTypeInstance());

        // model setter
        $simpleTypeInstance = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product\Type\Simple::class
        );
        $this->_model->setTypeInstance($simpleTypeInstance);
        $this->assertSame($simpleTypeInstance, $this->_model->getTypeInstance());
    }

    public function testGetIdBySku()
    {
        $this->assertGreaterThan(0, (int)$this->_model->getIdBySku('simple')); // fixture
    }

    public function testGetAttributes()
    {
        // fixture required
        $this->_model->load(1);
        $attributes = $this->_model->getAttributes();
        $this->assertArrayHasKey('name', $attributes);
        $this->assertArrayHasKey('sku', $attributes);
        $this->assertInstanceOf(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class, $attributes['sku']);
    }

    /**
     * @covers \Magento\Catalog\Model\Product::getCalculatedFinalPrice
     * @covers \Magento\Catalog\Model\Product::getMinimalPrice
     * @covers \Magento\Catalog\Model\Product::getSpecialPrice
     * @covers \Magento\Catalog\Model\Product::getSpecialFromDate
     * @covers \Magento\Catalog\Model\Product::getSpecialToDate
     * @covers \Magento\Catalog\Model\Product::getRequestPath
     * @covers \Magento\Catalog\Model\Product::getGiftMessageAvailable
     * @dataProvider getObsoleteGettersDataProvider
     * @param string $key
     * @param string $method
     */
    public function testGetObsoleteGetters($key, $method)
    {
        $value = uniqid();
        $this->assertEmpty($this->_model->{$method}());
        $this->_model->setData($key, $value);
        $this->assertEquals($value, $this->_model->{$method}());
    }

    public static function getObsoleteGettersDataProvider()
    {
        return [
            ['calculated_final_price', 'getCalculatedFinalPrice'],
            ['minimal_price', 'getMinimalPrice'],
            ['special_price', 'getSpecialPrice'],
            ['special_from_date', 'getSpecialFromDate'],
            ['special_to_date', 'getSpecialToDate'],
            ['request_path', 'getRequestPath'],
            ['gift_message_available', 'getGiftMessageAvailable'],
        ];
    }

    public function testGetMediaAttributes()
    {
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class,
            ['data' => ['media_attributes' => 'test']]
        );
        $this->assertEquals('test', $model->getMediaAttributes());

        $attributes = $this->_model->getMediaAttributes();
        $this->assertArrayHasKey('image', $attributes);
        $this->assertArrayHasKey('small_image', $attributes);
        $this->assertArrayHasKey('thumbnail', $attributes);
        $this->assertInstanceOf(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class, $attributes['image']);
    }

    public function testGetMediaGalleryImages()
    {
        /** @var $model \Magento\Catalog\Model\Product */
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );
        $this->assertEmpty($model->getMediaGalleryImages());

        $this->_model->setMediaGallery(['images' => [['file' => 'magento_image.jpg']]]);
        $images = $this->_model->getMediaGalleryImages();
        $this->assertInstanceOf(\Magento\Framework\Data\Collection::class, $images);
        foreach ($images as $image) {
            $this->assertInstanceOf(\Magento\Framework\DataObject::class, $image);
            $image = $image->getData();
            $this->assertArrayHasKey('file', $image);
            $this->assertArrayHasKey('url', $image);
            $this->assertArrayHasKey('id', $image);
            $this->assertArrayHasKey('path', $image);
            $this->assertStringEndsWith('magento_image.jpg', $image['file']);
            $this->assertStringEndsWith('magento_image.jpg', $image['url']);
            $this->assertStringEndsWith('magento_image.jpg', $image['path']);
        }
    }

    public function testGetMediaConfig()
    {
        $model = $this->_model->getMediaConfig();
        $this->assertInstanceOf(\Magento\Catalog\Model\Product\Media\Config::class, $model);
        $this->assertSame($model, $this->_model->getMediaConfig());
    }

    public function testGetAttributeText()
    {
        $this->assertNull($this->_model->getAttributeText('status'));
        $this->_model->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $this->assertEquals('Enabled', $this->_model->getAttributeText('status'));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products_with_multiselect_attribute.php
     */
    public function testGetAttributeTextArray()
    {
        $product = $this->productRepository->get('simple_ms_2');
        $product->getAttributeText('multiselect_attribute');
        $expected = [
            'Option 2',
            'Option 3',
            'Option 4 "!@#$%^&*'
        ];
        self::assertEquals(
            $expected,
            $product->getAttributeText('multiselect_attribute')
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products_with_multiselect_attribute.php
     */
    public function testMultipleMultiselectTextValues()
    {
        $expectedArray = [];

        for ($i = 1; $i < 200; $i++) {
            $expectedArray[] = sprintf('Multiselect option %d', $i);
        }

        $product = $this->productRepository->get('simple_ms_3');

        self::assertEquals(
            $expectedArray,
            $product->getAttributeText('multiselect_attribute_text')
        );
    }

    public function testGetCustomDesignDate()
    {
        $this->assertEquals(['from' => null, 'to' => null], $this->_model->getCustomDesignDate());
        $this->_model->setCustomDesignFrom(1)->setCustomDesignTo(2);
        $this->assertEquals(['from' => 1, 'to' => 2], $this->_model->getCustomDesignDate());
    }

    /**
     * @see \Magento\Catalog\Model\Product\Type\SimpleTest
     */
    public function testGetSku()
    {
        $this->assertEmpty($this->_model->getSku());
        $this->_model->setSku('sku');
        $this->assertEquals('sku', $this->_model->getSku());
    }

    public function testGetWeight()
    {
        $this->assertEmpty($this->_model->getWeight());
        $this->_model->setWeight(10.22);
        $this->assertEquals(10.22, $this->_model->getWeight());
    }

    public function testGetOptionInstance()
    {
        $model = $this->_model->getOptionInstance();
        $this->assertInstanceOf(\Magento\Catalog\Model\Product\Option::class, $model);
        $this->assertSame($model, $this->_model->getOptionInstance());
    }

    public function testGetDefaultAttributeSetId()
    {
        $setId = $this->_model->getDefaultAttributeSetId();
        $this->assertNotEmpty($setId);
        $this->assertMatchesRegularExpression('/^[0-9]+$/', $setId);
    }

    public function testGetPreconfiguredValues()
    {
        $this->assertInstanceOf(\Magento\Framework\DataObject::class, $this->_model->getPreconfiguredValues());
        $this->_model->setPreconfiguredValues('test');
        $this->assertEquals('test', $this->_model->getPreconfiguredValues());
    }

    public static function tearDownAfterClass(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $mediaDirectory = $objectManager->get(
            \Magento\Framework\Filesystem::class
        )->getDirectoryWrite(
            DirectoryList::MEDIA
        );
        $config = $objectManager->get(\Magento\Catalog\Model\Product\Media\Config::class);
        $mediaDirectory->delete($config->getBaseMediaPath());
    }
}
