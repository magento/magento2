<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Block\Account;

/**
 * Interface for sortable links.
 * @api
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
     */
    public function getSortOrder();
}
