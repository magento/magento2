<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Review\Test\Block\Adminhtml;

use Magento\Backend\Test\Block\Widget\Grid as GridAbstract;

/**
 * Class Grid
 * Reviews grid
 */
class Grid extends GridAbstract
{
    /**
     * Filters array mapping
     *
     * @var array
     */
    protected $filters = [
        'review_id' => [
            'selector' => 'input[name="review_id"]',
        ],
        'title' => [
            'selector' => 'input[name="title"]',
        ],
        'status' => [
            'selector' => '#reviwGrid_filter_status',
            'input' => 'select',
        ],
        'nickname' => [
            'selector' => 'input[name="nickname"]',
        ],
        'detail' => [
            'selector' => 'input[name="detail"]',
        ],
        'visible_in' => [
            'selector' => 'select[name="visible_in"]',
            'input' => 'selectstore',
        ],
        'type' => [
            'selector' => 'select[name="type"]',
            'input' => 'select',
        ],
        'name' => [
            'selector' => 'input[name="name"]',
        ],
        'sku' => [
            'selector' => 'input[name="sku"]',
        ],
    ];
}
