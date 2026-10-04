<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\View\Asset\Bundle;

use Magento\Framework\View\Asset\File\FallbackContext;

/**
 * Interface ConfigInterface
 * @deprecated 101.0.0
 * @see \Magento\Deploy\Config\BundleConfig
 */
interface ConfigInterface
{
    /**
     * @param FallbackContext $assetContext
     * @return bool
     */
    public function isSplit(FallbackContext $assetContext);

    /**
     * @param FallbackContext $assetContext
     * @return \Magento\Framework\Config\View
     */
    public function getConfig(FallbackContext $assetContext);

    /**
     * @param FallbackContext $assetContext
     * @return false|float|int|string
     */
    public function getPartSize(FallbackContext $assetContext);
}
