<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\View\Asset;

/**
 * An interface for "preprocessing" asset contents
 */
interface PreProcessorInterface
{
    /**
     * Transform content and/or content type for the specified preprocessing chain object
     *
     * @param \Magento\Framework\View\Asset\PreProcessor\Chain $chain
     * @return void
     */
    public function process(\Magento\Framework\View\Asset\PreProcessor\Chain $chain);
}
