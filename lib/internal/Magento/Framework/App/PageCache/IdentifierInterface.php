<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\PageCache;

/**
 * @api
 * @since 100.0.2
 */
interface IdentifierInterface
{
    /**
     * Return unique page identifier
     *
     * @return string
     */
    public function getValue(): string;
}
