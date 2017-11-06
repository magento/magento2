<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

/**
 * Tests product repository.
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class ProductRepositoryTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->productFactory = $this->objectManager->create(ProductFactory::class);
    }

    public static function tearDownAfterClass()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var Config $config */
        $config = $objectManager->get(Config::class);
        /** @var \Magento\Framework\Filesystem\Directory\WriteInterface $mediaDirectory */
        $mediaDirectory = $objectManager->get(
            Filesystem::class
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

    public function testAddImageToMediaGallery()
    {
        // Model accepts only files in tmp media path, we need to copy fixture file there
        $mediaFile = $this->_copyFileToBaseTmpMediaPath(dirname(__DIR__) . '/_files/magento_image.jpg');
        $this->productFactory = $this->objectManager->get(ProductFactory::class);
        $product = $this->productFactory->create()
            ->setName('test product')
            ->setSku('test product');
        $product->setAttributeSetId($product->getDefaultAttributeSetId());
        $product->addImageToMediaGallery(
            $mediaFile,
            [
                'image',
                'small_image',
                'thumbnail',
            ],
            false,
            false
        );
        $this->productRepository->save($product);
        $gallery = $product->getData('media_gallery');

        $this->assertNotEmpty($gallery);
        $this->assertTrue(isset($gallery['images'][0]['file']));
        $this->assertStringStartsWith('/m/a/magento_image', $gallery['images'][0]['file']);
        $this->assertTrue(isset($gallery['images'][0]['position']));
        $this->assertTrue(isset($gallery['images'][0]['disabled']));
        $this->assertArrayHasKey('label', $gallery['images'][0]);
    }

    /**
     * Copy file to media tmp directory and return it's name
     *
     * @param string $sourceFile
     * @return string
     */
    protected function _copyFileToBaseTmpMediaPath($sourceFile)
    {
        /** @var Config $config */
        $config = $this->objectManager->get(Config::class);
        /** @var \Magento\Framework\Filesystem\Directory\WriteInterface $mediaDirectory */
        $mediaDirectory = $this->objectManager->get(Filesystem::class)->getDirectoryWrite(DirectoryList::MEDIA);
        $mediaDirectory->create($config->getBaseTmpMediaPath());
        $targetFile = $config->getTmpMediaPath(basename($sourceFile));
        copy($sourceFile, $mediaDirectory->getAbsolutePath($targetFile));

        return $targetFile;
    }
}
