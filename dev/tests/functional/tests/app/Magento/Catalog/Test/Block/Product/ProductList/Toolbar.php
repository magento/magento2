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

namespace Magento\Catalog\Test\Block\Product\ProductList;

use Mtf\Block\Block;

/**
 * Class Toolbar
 * Toolbar the product list page
 */
class Toolbar extends Block
{
    /**
     * Selector next active element
     *
     * @var string
     */
    protected $nextPageSelector = '.item.current + .item a';

    /**
     * Selector for "sort by" element
     *
     * @var string
     */
    protected $sorter = '#sorter';

    /**
     * Go to the next page
     *
     * @return bool
     */
    public function nextPage()
    {
        $nextPageItem = $this->_rootElement->find($this->nextPageSelector);
        if ($nextPageItem->isVisible()) {
            $nextPageItem->click();
            return true;
        }

        return false;
    }

    /**
     * Get method of sorting product
     *
     * @return array|string
     */
    public function getSelectSortType()
    {
        return $this->_rootElement->find($this->sorter)->getValue();
    }

    /**
     * Get all available method of sorting product
     *
     * @return array|string
     */
    public function getSortType()
    {
        $content = str_replace("\r", '', $this->_rootElement->find($this->sorter)->getText());
        return explode("\n", $content);
    }
}
