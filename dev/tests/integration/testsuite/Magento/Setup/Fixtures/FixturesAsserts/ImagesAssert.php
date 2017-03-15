<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures\FixturesAsserts;

use Magento\Framework\App\Filesystem\DirectoryList;

class ImagesAssert
{

    private $mediaDirectory;

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

    public function assert()
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $products = $this->productRepository->getList($searchCriteria)->getItems();

        foreach ($products as $product) {
            $this->assertProductMediaGallery($product);
            $this->assertProductMediaAttributes($product);
            $this->assertProductImageExistsInFS($product);
        }
    }

    private function assertProductMediaGallery(\Magento\Catalog\Api\Data\ProductInterface $product)
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

    private function assertProductMediaAttributes(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        foreach ($product->getMediaAttributeValues() as $attributeCode => $attributeValue) {
            if (empty($attributeValue)) {
                throw new \AssertionError(
                    sprintf('Attribute: %s should not be empty', $attributeCode)
                );
            }
        }
    }

    private function assertProductImageExistsInFS(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $mediaDirectory = $this->getMediaDirectory();
        $mediaAttributes = $product->getMediaAttributeValues();

        if (!$mediaDirectory->isExist($this->mediaConfig->getBaseMediaPath() . $mediaAttributes['image'])) {
            throw new \AssertionError('Image file for product supposed to exist');
        }
    }

    private function getMediaDirectory()
    {
        if ($this->mediaDirectory === null) {
            $this->mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        }

        return $this->mediaDirectory;
    }
}
