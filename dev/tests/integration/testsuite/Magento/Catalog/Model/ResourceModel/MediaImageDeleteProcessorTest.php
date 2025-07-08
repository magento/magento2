<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class MediaImageDeleteProcessorTest extends TestCase
{
    /**
     * @var MediaImageDeleteProcessor|null
     */
    private ?MediaImageDeleteProcessor $mediaImageDeleteProcessor;

    /**
     * @var ProductRepositoryInterface|null
     */
    private ?ProductRepositoryInterface $productRepository;

    /**
     * @var ReadInterface|null
     */
    private ?ReadInterface $mediaDirectory;

    /**
     * @var Config|null
     */
    private ?Config $config;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $om = Bootstrap::getObjectManager();

        $this->mediaImageDeleteProcessor = $om->get(MediaImageDeleteProcessor::class);
        $this->productRepository = $om->get(ProductRepositoryInterface::class);

        $this->mediaDirectory = $om->get(Filesystem::class)->getDirectoryRead(DirectoryList::MEDIA);
        $this->config = $om->get(Config::class);
    }

    #[
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'simple',
                'media_gallery_entries' => [
                    []
                ]
            ]
        ),
    ]
    public function testOnlyImageFileDeleted()
    {
        $product = $this->productRepository->get('simple');
        $image = $product->getMediaGalleryEntries()[0];
        $imageFilePath = $this->config->getBaseMediaPath() . $image['file'];

        $this->assertTrue(
            $this->mediaDirectory->isExist($imageFilePath),
            'The image file not existed.'
        );
        $this->mediaImageDeleteProcessor->execute($product);
        $this->assertFalse(
            $this->mediaDirectory->isExist($imageFilePath),
            'The image file must be deleted.'
        );
    }
}
