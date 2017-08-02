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
 * @since 2.0.0
 */
interface AlternativeSourceInterface extends PreProcessorInterface
{
    /**
     * Get extensions names of alternatives
     *
     * @return string[]
     * @since 2.0.0
     */
    public function getAlternativesExtensionsNames();
}
