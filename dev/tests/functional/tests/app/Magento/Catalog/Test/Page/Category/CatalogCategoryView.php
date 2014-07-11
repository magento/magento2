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

namespace Magento\Catalog\Test\Page\Category;

use Mtf\Page\FrontendPage;

/**
 * Class CatalogCategoryView
 * Catalog Category page
 */
class CatalogCategoryView extends FrontendPage
{
    const MCA = 'catalog/category/view';

    protected $_blocks = [
        'listProductBlock' => [
            'name' => 'listProductBlock',
            'class' => 'Magento\Catalog\Test\Block\Product\ListProduct',
            'locator' => '.products.wrapper.grid',
            'strategy' => 'css selector',
        ],
        'mapBlock' => [
            'name' => 'mapBlock',
            'class' => 'Magento\Catalog\Test\Block\Product\Price',
            'locator' => '#map-popup-content',
            'strategy' => 'css selector',
        ],
        'layeredNavigationBlock' => [
            'name' => 'layeredNavigationBlock',
            'class' => 'Magento\LayeredNavigation\Test\Block\Navigation',
            'locator' => '.block.filter',
            'strategy' => 'css selector',
        ],
        'toolbar' => [
            'name' => 'toolbar',
            'class' => 'Magento\Catalog\Test\Block\Product\ProductList\Toolbar',
            'locator' => '.toolbar.products',
            'strategy' => 'css selector',
        ],
        'titleBlock' => [
            'name' => 'titleBlock',
            'class' => 'Magento\Theme\Test\Block\Html\Title',
            'locator' => '.page.title h1.title',
            'strategy' => 'css selector',
        ],
        'viewBlock' => [
            'name' => 'viewBlock',
            'class' => 'Magento\Catalog\Test\Block\Category\View',
            'locator' => '.category.view',
            'strategy' => 'css selector',
        ],
    ];

    /**
     * @return \Magento\Catalog\Test\Block\Product\ListProduct
     */
    public function getListProductBlock()
    {
        return $this->getBlockInstance('listProductBlock');
    }

    /**
     * @return \Magento\Catalog\Test\Block\Product\Price
     */
    public function getMapBlock()
    {
        return $this->getBlockInstance('mapBlock');
    }

    /**
     * @return \Magento\LayeredNavigation\Test\Block\Navigation
     */
    public function getLayeredNavigationBlock()
    {
        return $this->getBlockInstance('layeredNavigationBlock');
    }

    /**
     * @return \Magento\Catalog\Test\Block\Product\ProductList\Toolbar
     */
    public function getToolbar()
    {
        return $this->getBlockInstance('toolbar');
    }

    /**
     * @return \Magento\Theme\Test\Block\Html\Title
     */
    public function getTitleBlock()
    {
        return $this->getBlockInstance('titleBlock');
    }

    /**
     * @return \Magento\Catalog\Test\Block\Category\View
     */
    public function getViewBlock()
    {
        return $this->getBlockInstance('viewBlock');
    }
}
