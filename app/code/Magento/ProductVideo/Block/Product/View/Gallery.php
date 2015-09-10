<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Simple product data view
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\ProductVideo\Block\Product\View;

class Gallery extends \Magento\Catalog\Block\Product\View\Gallery
{
    /*
     * @var \Magento\ProductVideo\Helper\Media
     */
    protected $mediaHelper;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Stdlib\ArrayUtils $arrayUtils
     * @param \Magento\ProductVideo\Helper\Media $mediaHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        \Magento\ProductVideo\Helper\Media $mediaHelper,
        array $data = []
    ) {
        $this->mediaHelper = $mediaHelper;
        parent::__construct(
            $context,
            $arrayUtils,
            $data
        );
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
        return json_encode($mediaGalleryData);
    }

    /**
     * Retrieve video settings data in JSON format
     *
     * @return string
     */
    public function getVideoSettingsJson()
    {
        $videoSettingData[] = [
            'videoPlay' => $this->mediaHelper->getVideoPlayAttribute(),
            'videoStop' => $this->mediaHelper->getVideoStopAttribute(),
            'videoBackground' => $this->mediaHelper->getVideoBackgroundAttribute(),
        ];
        return json_encode($videoSettingData);
    }
}
