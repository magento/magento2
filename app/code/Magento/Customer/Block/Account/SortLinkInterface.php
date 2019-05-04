<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Block\Account;

/**
 * Interface for sortable links.
 * @api
 * @since 100.2.0
 */
interface SortLinkInterface
{
    /**#@+
     * Constant for confirmation status
     */
    const SORT_ORDER = 'sortOrder';
    /**#@-*/

    /**
     * Get sort order for block.
     *
     * @return int
     * @since 100.2.0
     */
    public function getSortOrder();
}
