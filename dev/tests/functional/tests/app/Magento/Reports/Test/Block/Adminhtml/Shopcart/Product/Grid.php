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

namespace Magento\Reports\Test\Block\Adminhtml\Shopcart\Product;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Mtf\Client\Element\Locator;

/**
 * Class Grid
 * Products in Carts Report grid
 */
class Grid extends \Magento\Backend\Test\Block\Widget\Grid
{
    /**
     * Product row selector
     *
     * @var string
     */
    protected $productRow = '//tr[td[contains(@class,"col-name")] and contains(.,"%s")]';

    /**
     * Product price selector
     *
     * @var string
     */
    protected $productPrice =  '//td[contains(@class,"col-price") and contains(.,"%s")]';

    /**
     * Product carts selector
     *
     * @var string
     */
    protected $productCarts =  '//td[contains(@class,"col-carts") and contains(.,"%d")]';

    /**
     * Check that product visible in grid
     *
     * @param CatalogProductSimple $product
     * @param string $carts
     * @return bool
     */
    public function isProductVisible(CatalogProductSimple $product, $carts)
    {
        $result = false;
        $productRowSelector = sprintf($this->productRow, $product->getName());
        $productPrice = sprintf($this->productPrice, $product->getPrice());
        $productRow = $this->_rootElement->find($productRowSelector, Locator::SELECTOR_XPATH);
        if ($productRow->isVisible()) {
            $result = $productRow->find($productPrice, Locator::SELECTOR_XPATH)->isVisible()
                && $productRow->find(sprintf($this->productCarts, $carts), Locator::SELECTOR_XPATH)->isVisible();
        }

        return $result;
    }
}
