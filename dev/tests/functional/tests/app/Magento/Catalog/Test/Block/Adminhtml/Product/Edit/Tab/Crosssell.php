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

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab;

use Mtf\Client\Element;
use Magento\Backend\Test\Block\Widget\Tab;
use Mtf\Factory\Factory;

/**
 * Class Crosssell
 * Cross-sell Tab
 */
class Crosssell extends Tab
{
    const GROUP = 'crosssells';

    /**
     * Select cross-sells products
     *
     * @param array $products
     * @param Element|null $context
     * @return $this
     */
    public function fillFormTab(array $products, Element $context = null)
    {
        if (!isset($products['crosssell_products'])) {
            return $this;
        }
        $element = $context ? : $this->_rootElement;
        $crossSellBlock = Factory::getBlockFactory()->getMagentoCatalogAdminhtmlProductEditTabCrosssellGrid(
            $element->find('#cross_sell_product_grid')
        );
        foreach ($products['crosssell_products']['value'] as $product) {
            $crossSellBlock->searchAndSelect($product);
        }

        return $this;
    }
}
