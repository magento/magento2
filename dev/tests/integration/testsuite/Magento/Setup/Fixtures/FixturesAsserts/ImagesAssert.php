<?php
/**
 * Copyright © 2013-2018 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures\FixturesAsserts;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class ImagesAssert
 *
 * Class performs assertions to check that generated images are valid
 * after running setup:performance:generate-fixtures command
 */
class ImagesAssert
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Catalog\Model\Product\Gallery\ReadHandler
     */
    private $readHandler;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    private $mediaConfig;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    private $mediaDirectory;

    /**
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Model\Product\Gallery\ReadHandler $readHandler
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
     */
    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\Product\Gallery\ReadHandler $readHandler,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig
    ) {
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->readHandler = $readHandler;
        $this->filesystem = $filesystem;
        $this->mediaConfig = $mediaConfig;
    }

    /**
     * Performs assertions over images
     *
     * @return bool
     * @throws \AssertionError
     */
    public function assert()
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $products = $this->productRepository->getList($searchCriteria)->getItems();

        foreach ($products as $product) {
            $this->assertProductMediaGallery($product);
            $this->assertProductMediaAttributes($product);
            $this->assertProductImageExistsInFS($product);
        }

        return true;
    }

    /**
     * Performs assertions over media_gallery product attribute
     *
     * @param \Magento\Catalog\Model\Product $product
     * @throws \AssertionError
     */
    private function assertProductMediaGallery(\Magento\Catalog\Model\Product $product)
    {
        $extendedProduct = $this->readHandler->execute($product);
        $mediaGalleryImages = $extendedProduct->getMediaGalleryEntries();

        if (count($mediaGalleryImages) !== 1) {
            throw new \AssertionError('Product supposed to contain one image');
        }

        $image = reset($mediaGalleryImages);

        if ($image->getFile() === null) {
            throw new \AssertionError('Image path should not be null');
        }
    }

    /**
     * Performs assertions over product media attributes
     * e.g. image|small_image|swatch_image|thumbnail
     *
     * @param \Magento\Catalog\Model\Product $product
     * @throws \AssertionError
     */
    private function assertProductMediaAttributes(\Magento\Catalog\Model\Product $product)
    {
        foreach ($product->getMediaAttributeValues() as $attributeCode => $attributeValue) {
            if (empty($attributeValue)) {
                throw new \AssertionError(
                    sprintf('Attribute: %s should not be empty', $attributeCode)
                );
            }
        }
    }

    /**
     * Performs assertions over image files in FS
     *
     * @param \Magento\Catalog\Model\Product $product
     * @throws \AssertionError
     */
    private function assertProductImageExistsInFS(\Magento\Catalog\Model\Product $product)
    {
        $mediaDirectory = $this->getMediaDirectory();
        $mediaAttributes = $product->getMediaAttributeValues();

        if (!$mediaDirectory->isExist($this->mediaConfig->getBaseMediaPath() . $mediaAttributes['image'])) {
            throw new \AssertionError('Image file for product supposed to exist');
        }
    }

    /**
     * Local cache for $mediaDirectory
     *
     * @return \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    private function getMediaDirectory()
    {
        if ($this->mediaDirectory === null) {
            $this->mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        }

        return $this->mediaDirectory;
    }
}
