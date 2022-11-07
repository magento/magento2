<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallerySynchronization\Plugin;

use Magento\Framework\App\Config\Value;
use Magento\MediaGallerySynchronization\Model\Publish;

/**
 * Plugin to synchronize media storage and media assets database records when media gallery enabled in configuration.
 */
class MediaGallerySyncTrigger
{
    private const MEDIA_GALLERY_CONFIG_VALUE = 'system/media_gallery/enabled';
    private const MEDIA_GALLERY_ENABLED_VALUE = 1;

    /**
     * @var Publish
     */
    private $publish;

    /**
     * @param Publish $publish
     */
    public function __construct(Publish $publish)
    {
        $this->publish = $publish;
    }

    /**
     * Update media gallery grid table when configuration is saved and media gallery enabled.
     *
     * @param Value $config
     * @param Value $result
     * @return Value
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(Value $config, Value $result): Value
    {
        if ($result->getPath() === self::MEDIA_GALLERY_CONFIG_VALUE
            && $result->isValueChanged()
            && (int) $result->getValue() === self::MEDIA_GALLERY_ENABLED_VALUE
        ) {
            $this->publish->execute();
        }

        return $result;
    }
}
