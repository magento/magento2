<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset\PreProcessor;

use Magento\Framework\View\Asset\PreProcessorInterface;

/**
 * Interface AlternativeSourceInterface
 *
 * @api
 * @since 100.0.2
 */
interface AlternativeSourceInterface extends PreProcessorInterface
{
    /**
     * Get extensions names of alternatives
     *
     * @return string[]
     */
    public function getAlternativesExtensionsNames();
}
