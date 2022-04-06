<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\State;
use Magento\MediaStorage\Service\ImageResize;
use Magento\MediaStorage\Service\ImageResizeScheduler;
use Magento\Catalog\Model\Config\CatalogMediaConfig;

/**
 * Resize product images after the product is saved
 */
class ImageResizeAfterProductSave implements ObserverInterface
{
    /**
     * @var ImageResize
     */
    private $imageResize;

    /**
     * @var State
     */
    private $state;

    /**
     * @var CatalogMediaConfig
     */
    private $catalogMediaConfig;

    /**
     * @var ImageResizeScheduler
     */
    private $imageResizeScheduler;

    /**
     * @var bool
     */
    private $imageResizeSchedulerFlag = false;

    /**
     * Product constructor.
     *
     * @param ImageResize $imageResize
     * @param State $state
     * @param CatalogMediaConfig $catalogMediaConfig
     * @param ImageResizeScheduler $imageResizeScheduler
     * @param bool $imageResizeSchedulerFlag
     */
    public function __construct(
        ImageResize $imageResize,
        State $state,
        CatalogMediaConfig $catalogMediaConfig,
        ImageResizeScheduler $imageResizeScheduler,
        bool $imageResizeSchedulerFlag = false
    ) {
        $this->imageResize = $imageResize;
        $this->state = $state;
        $this->catalogMediaConfig = $catalogMediaConfig;
        $this->imageResizeScheduler = $imageResizeScheduler;
        $this->imageResizeSchedulerFlag = $imageResizeSchedulerFlag;
    }

    /**
     * Process event on 'save_commit_after' event
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $catalogMediaUrlFormat = $this->catalogMediaConfig->getMediaUrlFormat();
        if ($catalogMediaUrlFormat == CatalogMediaConfig::IMAGE_OPTIMIZATION_PARAMETERS) {
            // Skip image resizing on the Magento side when it is offloaded to a web server or CDN
            return;
        }

        /** @var $product \Magento\Catalog\Model\Product */
        $product = $observer->getEvent()->getProduct();

        if ($this->state->isAreaCodeEmulated()) {
            return;
        }

        if (!(bool) $product->getId()) {
            foreach ($product->getMediaGalleryImages() as $image) {
                $this->resizeImage($image->getFile());
            }
        } else {
            $new = $product->getData('media_gallery');
            $original = $product->getOrigData('media_gallery');
            $mediaGallery = !empty($new['images']) ? array_column($new['images'], 'file') : [];
            $mediaOriginalGallery = !empty($original['images']) ? array_column($original['images'], 'file') : [];

            foreach (array_diff($mediaGallery, $mediaOriginalGallery) as $image) {
                $this->resizeImage($image);
            }
        }
    }

    /**
     * Resize image in synchronous or asynchronous way
     *
     * @param string $image
     */
    private function resizeImage(string $image): void
    {
        if ($this->imageResizeSchedulerFlag) {
            $this->imageResizeScheduler->schedule($image);
        } else {
            $this->imageResize->resizeFromImageName($image);
        }
    }
}
