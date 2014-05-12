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

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Related;

use Mtf\Client\Element;
use Mtf\Factory\Factory;
use Mtf\Client\Element\Locator;
use Magento\Backend\Test\Block\Widget\Grid as GridInterface;

class Grid extends GridInterface
{
    protected $filters = array(
        'name' => array(
            'selector' => '#related_product_grid_filter_name'
        ),
        'sku' => array(
            'selector' => '#related_product_grid_filter_sku'
        ),
        'type' => array(
            'selector' => '#related_product_grid_filter_type',
            'input' => 'select'
        )
    );

    /**
     * @param array $filter
     */
    public function searchAndSelect(array $filter)
    {
        $element = $this->_rootElement;
        $resetButton = $this->resetButton;
        $this->_rootElement->waitUntil(
            function () use ($element, $resetButton) {
                return $element->find($resetButton)->isVisible() ? true : null;
            }
        );
        parent::searchAndSelect($filter);
    }
}
