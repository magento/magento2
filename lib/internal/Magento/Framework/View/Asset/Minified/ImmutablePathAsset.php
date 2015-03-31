<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset\Minified;

class ImmutablePathAsset extends AbstractAsset
{

    /**
     * Generate minified file and fill the properties to reference that file
     *
     * @return void
     */
    protected function fillPropertiesByMinifyingAsset()
    {
        $this->context = $this->originalAsset->getContext();
        $this->filePath = $this->originalAsset->getFilePath();
        $this->path = $this->originalAsset->getPath();
        $this->minify();
        $this->file = $this->staticViewDir->getAbsolutePath($this->path);
        $this->url = $this->context->getBaseUrl() . $this->path;
    }
}
