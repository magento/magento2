<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset\Minified;

use Magento\Framework\App\Filesystem\DirectoryList;

class MutablePathAsset extends AbstractAsset
{
    /**
     * Generate minified file and fill the properties to reference that file
     *
     * @return void
     */
    protected function fillPropertiesByMinifyingAsset()
    {
        $path = $this->originalAsset->getPath();
        $this->context = new \Magento\Framework\View\Asset\File\Context(
            $this->baseUrl->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_STATIC]),
            DirectoryList::STATIC_VIEW,
            self::CACHE_VIEW_REL . '/minified'
        );
        $this->filePath = md5($path) . '_' . $this->composeMinifiedName(basename($path));
        $this->path = $this->context->getPath() . '/' . $this->filePath;
        $this->minify();
        $this->file = $this->staticViewDir->getAbsolutePath($this->path);
        $this->url = $this->context->getBaseUrl() . $this->path;
    }
}
