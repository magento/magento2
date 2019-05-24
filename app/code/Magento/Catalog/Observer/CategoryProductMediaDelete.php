<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\Product\Media\ConfigInterface as MediaConfig;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class CategoryProductMediaDelete implements ObserverInterface
{
    /**
     * @var MediaConfig
     */

    private $imageConfig;
    /**
     * @var Filesystem
     */

    private $mediaDirectory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Product constructor.
     * @param MediaConfig $imageConfig
     * @param Filesystem $filesystem
     */
    public function __construct(
        MediaConfig $imageConfig,
        Filesystem $filesystem
    ) {
        $this->imageConfig = $imageConfig;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * Process event on 'catalog_product_delete_after_done' event
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $observer->getEvent()->getProduct();

        if ($product->getId()) {
            $mediaGallery = $product->getData('media_gallery');
            $productImages = !empty($mediaGallery['images']) ? array_column($mediaGallery['images'], 'file') : [];

            foreach ($productImages as $image) {
                $originalImagePath = $this->mediaDirectory->getAbsolutePath(
                    $this->imageConfig->getMediaPath($image)
                );

                if (file_exists($originalImagePath)) {
                    unlink($originalImagePath);
                }
            }
        }
    }
}
