<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Gallery;

use Magento\Framework\Exception\FileSystemException;

/**
 * Test class for \Magento\Catalog\Model\Product\Gallery\CreateHandler.
 *
 * @magentoDataFixture Magento/Catalog/_files/product_simple.php
 * @magentoDataFixture Magento/Catalog/_files/product_image.php
 */
class CreateHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Create handler for catalog product gallery.
     *
     * @var \Magento\Catalog\Model\Product\Gallery\CreateHandler
     */
    protected $createHandler;

    protected function setUp()
    {
        $this->createHandler = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product\Gallery\CreateHandler::class
        );
    }

    /**
     * @covers \Magento\Catalog\Model\Product\Gallery\CreateHandler::execute
     */
    public function testExecute()
    {
        $fileName = '/m/a/magento_image.jpg';
        $fileLabel = 'Magento image';
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );
        $product->load(1);
        $product->setData(
            'media_gallery',
            ['images' => ['image' => ['file' => $fileName, 'label' => $fileLabel]]]
        );
        $product->setData('image', $fileName);
        $this->createHandler->execute($product);
        $this->assertStringStartsWith('/m/a/magento_image', $product->getData('media_gallery/images/image/new_file'));
        $this->assertEquals($fileLabel, $product->getData('image_label'));

        $product->setIsDuplicate(true);
        $product->setData(
            'media_gallery',
            ['images' => ['image' => ['value_id' => '100', 'file' => $fileName, 'label' => $fileLabel]]]
        );
        $this->createHandler->execute($product);
        $this->assertStringStartsWith('/m/a/magento_image', $product->getData('media_gallery/duplicate/100'));
        $this->assertEquals($fileLabel, $product->getData('image_label'));
    }
}
