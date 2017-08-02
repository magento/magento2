<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset\Bundle;

use Magento\Framework\View\Asset\File\FallbackContext;

/**
 * Interface ConfigInterface
 * @deprecated 2.2.0 since 2.2.0
 * @see \Magento\Deploy\Config\BundleConfig
 * @since 2.0.0
 */
interface ConfigInterface
{
    /**
     * @param FallbackContext $assetContext
     * @return bool
     * @since 2.0.0
     */
    public function isSplit(FallbackContext $assetContext);

    /**
     * @param FallbackContext $assetContext
     * @return \Magento\Framework\Config\View
     * @since 2.0.0
     */
    public function getConfig(FallbackContext $assetContext);

    /**
     * @param FallbackContext $assetContext
     * @return false|float|int|string
     * @since 2.0.0
     */
    public function getPartSize(FallbackContext $assetContext);
}
