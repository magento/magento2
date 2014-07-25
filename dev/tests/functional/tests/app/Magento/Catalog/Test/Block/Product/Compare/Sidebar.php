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

namespace Magento\Catalog\Test\Block\Product\Compare;

use Mtf\Client\Element;

/**
 * Class Sidebar
 * Compare product block on cms page
 */
class Sidebar extends ListCompare
{
    /**
     * Selector for empty message
     *
     * @var string
     */
    protected $isEmpty = 'p.empty';

    /**
     * Product name selector
     *
     * @var string
     */
    protected $productName = 'li.item.odd.last strong.name a';

    /**
     * Selector for "Clear All" button
     *
     * @var string
     */
    protected $clearAll = '#compare-clear-all';

    /**
     * Get compare products block content
     *
     * @return array|string
     */
    public function getProducts()
    {
        $result = [];
        $isEmpty = $this->_rootElement->find($this->isEmpty);
        if ($isEmpty->isVisible()) {
            return $isEmpty->getText();
        }
        $elements = $this->_rootElement->find($this->productName)->getElements();
        foreach ($elements as $element) {
            $result[] = $element->getText();
        }
        return $result;
    }

    /**
     * Click "Clear All" on "My Account" page
     *
     * @return void
     */
    public function clickClearAll()
    {
        $this->_rootElement->find($this->clearAll)->click();
        $this->_rootElement->acceptAlert();
    }
}
