<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset;

/**
 * An abstraction for getting context path of an asset
 * @since 2.0.0
 */
interface ContextInterface
{
    /**
     * Get context path of an asset
     *
     * @return string
     * @since 2.0.0
     */
    public function getPath();

    /**
     * Get base URL
     *
     * @return string
     * @since 2.0.0
     */
    public function getBaseUrl();
}
