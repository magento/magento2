<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset;

/**
 * An interface for "preprocessing" asset contents
 *
 * @api
 * @since 100.0.2
 */
interface PreProcessorInterface
{
    /**
     * Transform content and/or content type for the specified preprocessing chain object
     *
     * @param PreProcessor\Chain $chain
     * @return void
     */
    public function process(PreProcessor\Chain $chain);
}
