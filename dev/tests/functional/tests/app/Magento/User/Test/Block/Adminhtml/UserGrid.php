<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\User\Test\Block\Adminhtml;

use Magento\Backend\Test\Block\Widget\Grid;

/**
 * Class UserGrid
 * User grid on User index page.
 */
class UserGrid extends Grid
{
    /**
     * Grid filters' selectors
     *
     * @var array
     */
    protected $filters = [
        'username' => [
            'selector' => '#permissionsUserGrid_filter_username',
        ],
        'email' => [
            'selector' => '#permissionsUserGrid_filter_email',
        ],
    ];

    /**
     * Locator value of td with username
     *
     * @var string
     */
    protected $editLink = '[data-column="username"]';
}
