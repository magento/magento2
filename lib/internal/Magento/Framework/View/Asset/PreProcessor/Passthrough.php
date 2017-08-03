<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset\PreProcessor;

use Magento\Framework\View\Asset\PreProcessorInterface;

/**
 * Class Passthrough
 * @since 2.0.0
 */
class Passthrough implements PreProcessorInterface
{
    /**
     * Transform content and/or content type for the specified preprocessing chain object
     *
     * @param Chain $chain
     * @return void
     * @since 2.0.0
     */
    public function process(Chain $chain)
    {
        $chain->setContentType($chain->getTargetContentType());
    }
}
