<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Simple product data view
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\ProductVideo\Block\Product\View;

/**
 * @api
 */
class Gallery extends \Magento\Catalog\Block\Product\View\Gallery
{
    /**
     * @var \Magento\ProductVideo\Helper\Media
     */
    protected $mediaHelper;

    /**
     * @param \Magento\Catalog\Block\Product\Context                              $context
     * @param \Magento\Framework\Stdlib\ArrayUtils                                $arrayUtils
     * @param \Magento\Framework\Json\EncoderInterface                            $jsonEncoder
     * @param \Magento\ProductVideo\Helper\Media                                  $mediaHelper
     * @param \Magento\Catalog\Model\Product\Gallery\ImagesConfigFactoryInterface $imagesConfigFactory
     * @param array                                                               $galleryImagesConfig
     * @param array                                                               $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\ProductVideo\Helper\Media $mediaHelper,
        array $data = [],
        \Magento\Catalog\Model\Product\Gallery\ImagesConfigFactoryInterface $imagesConfigFactory = null,
        array $galleryImagesConfig = []
    ) {
        parent::__construct(
            $context,
            $arrayUtils,
            $jsonEncoder,
            $imagesConfigFactory,
            $galleryImagesConfig,
            $data
        );
        $this->mediaHelper = $mediaHelper;
    }

    /**
     * Retrieve media gallery data in JSON format
     *
     * @return string
     */
    public function getMediaGalleryDataJson()
    {
        $mediaGalleryData = [];
        foreach ($this->getProduct()->getMediaGalleryImages() as $mediaGalleryImage) {
            $mediaGalleryData[] = [
                'mediaType' => $mediaGalleryImage->getMediaType(),
                'videoUrl' => $mediaGalleryImage->getVideoUrl(),
                'isBase' => $this->isMainImage($mediaGalleryImage),
            ];
        }
        return $this->jsonEncoder->encode($mediaGalleryData);
    }

    /**
     * Retrieve video settings data in JSON format
     *
     * @return string
     */
    public function getVideoSettingsJson()
    {
        $videoSettingData[] = [
            'playIfBase' => $this->mediaHelper->getPlayIfBaseAttribute(),
            'showRelated' => $this->mediaHelper->getShowRelatedAttribute(),
            'videoAutoRestart' => $this->mediaHelper->getVideoAutoRestartAttribute(),
        ];
        return $this->jsonEncoder->encode($videoSettingData);
    }

    /**
     * Return media gallery for product options
     * @return string
     */
    public function getOptionsMediaGalleryDataJson()
    {
        return  $this->jsonEncoder->encode([]);
    }
}
