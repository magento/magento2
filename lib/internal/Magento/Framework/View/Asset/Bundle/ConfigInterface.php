<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
}
