<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Framework\View\Asset;

/**
 * An abstraction for getting context path of an asset
 */
interface ContextInterface
{
    /**
     * Get context path of an asset
     *
     * @return string
     */
    public function getPath();

    /**
     * Get base URL
     *
     * @return string
     */
    public function getBaseUrl();
}
