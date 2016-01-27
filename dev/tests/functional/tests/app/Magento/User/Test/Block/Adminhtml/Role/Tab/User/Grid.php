<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Block\Adminhtml\Role\Tab\User;

use Magento\Backend\Test\Block\Widget\Grid as AbstractGrid;

/**
 * Class Grid
 * Users grid in roles users tab
 */
class Grid extends AbstractGrid
{
    /**
     * Grid filters' selectors
     *
     * @var array
     */
    protected $filters = [
        'username' => [
            'selector' => 'input[name="role_user_username"]',
        ],
    ];

    /**
     * Locator value for role name column
     *
     * @var string
     */
    protected $selectItem = '.col-in_role_users input';
}
