<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\View\Asset\PreProcessor;

use Magento\Framework\View\Asset\PreProcessorInterface;

/**
 * Class Passthrough
 */
class Passthrough implements PreProcessorInterface
{
    /**
     * Transform content and/or content type for the specified preprocessing chain object
     *
     * @param Chain $chain
     * @return void
     */
    public function process(Chain $chain)
    {
        $chain->setContentType($chain->getTargetContentType());
    }
}
