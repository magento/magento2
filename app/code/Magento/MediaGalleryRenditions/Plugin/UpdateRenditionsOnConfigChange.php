<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryRenditions\Plugin;

use Magento\Framework\App\Config\Value;
use Magento\MediaGalleryRenditions\Model\Queue\ScheduleRenditionsUpdate;

/**
 * Update renditions if corresponding configuration changes
 */
class UpdateRenditionsOnConfigChange
{
    private const XML_PATH_MEDIA_GALLERY_RENDITIONS_WIDTH_PATH = 'system/media_gallery_renditions/width';
    private const XML_PATH_MEDIA_GALLERY_RENDITIONS_HEIGHT_PATH = 'system/media_gallery_renditions/height';

    /**
     * @var ScheduleRenditionsUpdate
     */
    private $scheduleRenditionsUpdate;

    /**
     * @param ScheduleRenditionsUpdate $scheduleRenditionsUpdate
     */
    public function __construct(ScheduleRenditionsUpdate $scheduleRenditionsUpdate)
    {
        $this->scheduleRenditionsUpdate = $scheduleRenditionsUpdate;
    }

    /**
     * Update renditions when configuration is changed
     *
     * @param Value $config
     * @param Value $result
     * @return Value
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(Value $config, Value $result): Value
    {
        if ($this->isRenditionsValue($result) && $result->isValueChanged()) {
            $this->scheduleRenditionsUpdate->execute();
        }

        return $result;
    }

    /**
     * Does configuration value relates to renditions
     *
     * @param Value $value
     * @return bool
     */
    private function isRenditionsValue(Value $value)
    {
        return $value->getPath() === self::XML_PATH_MEDIA_GALLERY_RENDITIONS_WIDTH_PATH
            || $value->getPath() === self::XML_PATH_MEDIA_GALLERY_RENDITIONS_HEIGHT_PATH;
    }
}
