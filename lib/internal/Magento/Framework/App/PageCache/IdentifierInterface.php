<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\PageCache;

/**
 * Page unique identifier interface
 */
interface IdentifierInterface
{
    /**
     * Return unique page identifier
     *
     * @return string
     */
    public function getValue();
}
