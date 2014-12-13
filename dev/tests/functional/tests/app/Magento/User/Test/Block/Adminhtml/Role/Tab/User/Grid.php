<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
    protected $selectItem = '.col-in_role_users > input';
}
