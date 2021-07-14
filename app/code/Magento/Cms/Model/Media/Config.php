<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Model\Media;

use Magento\Cms\Model\Wysiwyg\Config as CmsWysiwygConfig;
use Magento\MediaStorage\Model\Media\ConfigInterface;

/**
 * Media path config for CMS.
 */
class Config implements ConfigInterface
{
    /**
     * @inheritdoc
     */
    public function getBaseMediaPath(): string
    {
        return CmsWysiwygConfig::IMAGE_DIRECTORY;
    }

    /**
     * @inheritdoc
     */
    public function getMediaPath(string $file): string
    {
        return $this->getBaseMediaPath() . '/' . $this->prepareFile($file);
    }

    /**
     * Process file path.
     *
     * @param string $file
     * @return string
     */
    private function prepareFile(string $file): string
    {
        return ltrim(str_replace('\\', '/', $file), '/');
    }
}
