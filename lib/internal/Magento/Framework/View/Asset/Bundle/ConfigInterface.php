<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset\Bundle;

use Magento\Framework\View\Asset\File\FallbackContext;

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
