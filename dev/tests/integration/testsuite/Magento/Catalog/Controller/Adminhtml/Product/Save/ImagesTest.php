<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product\Save;

use Magento\Catalog\Model\Product\Media\Config;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * @magentoAppArea adminhtml
 */
class ImagesTest extends AbstractBackendController
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->config = $this->_objectManager->get(Config::class);
        $this->mediaDirectory = $this->_objectManager->get(Filesystem::class)->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * Test save product with default image.
     *
     * @dataProvider simpleProductImagesDataProvider
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDbIsolation enabled
     * @param array $postData
     * @param array $expectation
     * @return void
     */
    public function testSaveSimpleProductDefaultImage(array $postData, array $expectation): void
    {
        $this->copyFileToBaseTmpMediaPath();
        /** @var ProductRepository $repository */
        $repository = $this->_objectManager->create(ProductRepository::class);
        $product = $repository->get('simple');
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($postData);
        $this->dispatch('backend/catalog/product/save/id/' . $product->getEntityId());
        $product = $repository->get('simple', false, null, true);
        $galleryImage = reset($product->getData('media_gallery')['images']);
        $expectedGalleryImage = $expectation['media_gallery_image'];
        $this->assertEquals($expectedGalleryImage['position'], $galleryImage['position']);
        $this->assertEquals($expectedGalleryImage['media_type'], $galleryImage['media_type']);
        $this->assertEquals($expectedGalleryImage['label'], $galleryImage['label']);
        $this->assertEquals($expectedGalleryImage['disabled'], $galleryImage['disabled']);
        $this->assertEquals($expectedGalleryImage['file'], $galleryImage['file']);
        $this->assertEquals($expectation['image'], $product->getData('image'));
        $this->assertEquals($expectation['small_image'], $product->getData('small_image'));
        $this->assertEquals($expectation['thumbnail'], $product->getData('thumbnail'));
        $this->assertEquals($expectation['swatch_image'], $product->getData('swatch_image'));
        $this->assertFileExists(
            $this->mediaDirectory->getAbsolutePath($this->config->getBaseMediaPath() . $expectation['image'])
        );
    }

    /**
     * @return array
     */
    public function simpleProductImagesDataProvider(): array
    {
        return [
            'simple_product_with_jpg_image' => [
                'post_data' => [
                    'product' => [
                        'media_gallery' => [
                            'images' => [
                                'lrwuv5ukisn' => [
                                    'position' => '1',
                                    'media_type' => 'image',
                                    'video_provider' => '',
                                    'file' => '/m/a//magento_image.jpg.tmp',
                                    'value_id' => '',
                                    'label' => '',
                                    'disabled' => '0',
                                    'removed' => '',
                                    'role' => '',
                                ],
                            ],
                        ],
                        'image' => '/m/a//magento_image.jpg.tmp',
                        'small_image' => '/m/a//magento_image.jpg.tmp',
                        'thumbnail' => '/m/a//magento_image.jpg.tmp',
                        'swatch_image' => '/m/a//magento_image.jpg.tmp',
                    ],
                ],
                'expectation' => [
                    'media_gallery_image' => [
                        'position' => '1',
                        'media_type' => 'image',
                        'file' => '/m/a/magento_image.jpg',
                        'label' => '',
                        'disabled' => '0',
                    ],
                    'image' => '/m/a/magento_image.jpg',
                    'small_image' => '/m/a/magento_image.jpg',
                    'thumbnail' => '/m/a/magento_image.jpg',
                    'swatch_image' => '/m/a/magento_image.jpg',
                ]
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        $this->mediaDirectory->delete('tmp');
        $this->mediaDirectory->delete($this->config->getBaseMediaPath() . '/m/a/magento_image.jpg');
        parent::tearDown();
    }

    /**
     * Copy file to media tmp directory
     *
     * @return void
     */
    private function copyFileToBaseTmpMediaPath(): void
    {
        $sourceFile = realpath(__DIR__ . '/../../../../_files') . '/magento_image.jpg';
        $this->mediaDirectory->create($this->config->getBaseMediaPath() . '/m/a/');
        $this->mediaDirectory->create($this->config->getBaseTmpMediaPath() . '/m/a/');
        $targetFile = $this->config->getTmpMediaPath('/m/a/' . basename($sourceFile));
        copy($sourceFile, $this->mediaDirectory->getAbsolutePath($targetFile));
    }
}
