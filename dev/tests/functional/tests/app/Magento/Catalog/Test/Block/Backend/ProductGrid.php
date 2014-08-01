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
 * @spi
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Test\Block\Backend;

use Magento\Backend\Test\Block\Widget\Grid;

/**
 * Class ProductGrid
 * Backend catalog product grid
 *
 */
class ProductGrid extends Grid
{
    /**
     * Initialize block elements
     */
    protected $filters = array(
        'name' => array(
            'selector' => '#productGrid_product_filter_name'
        ),
        'sku' => array(
            'selector' => '#productGrid_product_filter_sku'
        ),
        'type' => array(
            'selector' => '#productGrid_product_filter_type',
            'input' => 'select'
        ),
        'price_from' => array(
            'selector' => '#productGrid_product_filter_price_from'
        ),
        'price_to' => array(
            'selector' => '#productGrid_product_filter_price_to'
        )
    );

    /**
     * Update attributes for selected items
     *
     * @param array $items
     */
    public function updateAttributes(array $items = array())
    {
        $this->massaction($items, 'Update Attributes');
    }
}
