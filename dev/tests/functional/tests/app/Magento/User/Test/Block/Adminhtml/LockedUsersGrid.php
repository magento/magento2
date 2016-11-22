<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Block\Adminhtml;

use Magento\Backend\Test\Block\Widget\Grid;

/**
 * Locked users grid on user locks page.
 */
class LockedUsersGrid extends Grid
{
    /**
     * Grid filters' selectors.
     *
     * @var array
     */
    protected $filters = [
        'username' => [
            'selector' => '#lockedAdminsGrid_filter_username',
        ],
    ];
}
