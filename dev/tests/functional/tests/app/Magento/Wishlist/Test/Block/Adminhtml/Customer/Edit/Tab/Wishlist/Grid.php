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

namespace Magento\Wishlist\Test\Block\Adminhtml\Customer\Edit\Tab\Wishlist;

use Magento\Backend\Test\Block\Widget\Grid as ParentGrid;
use Mtf\Client\Element\Locator;

/**
 * Class Grid
 * Grid on Wishlist tab in customer details on backend
 */
class Grid extends ParentGrid
{
    /**
     * Grid fields map
     *
     * @var array
     */
    protected $filters = [
        'product_name' => [
            'selector' => 'input[name="product_name"]'
        ],
    ];

    /**
     * Delete link selector
     *
     * @var string
     */
    protected $deleteLink = 'a[onclick*="removeItem"]';

    /**
     * Search item and delete it
     *
     * @param array $filter
     * @return void
     * @throws \Exception
     */
    public function searchAndDelete(array $filter)
    {
        $this->search($filter);
        $rowItem = $this->_rootElement->find($this->rowItem, Locator::SELECTOR_CSS);
        $rowItem->find($this->deleteLink, Locator::SELECTOR_CSS)->click();
        $this->_rootElement->acceptAlert();
    }
}
