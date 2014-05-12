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

use Mtf\Client\Element\Locator;
use Mtf\Factory\Factory;
use Mtf\Page\Page;

/**
 * Class CatalogCategoryView
 * Category page on frontend
 *
 */
class CatalogCategoryView extends Page
{
    /**
     * URL for category page
     */
    const MCA = 'catalog/category/view';

    /**
     * List of results of product search
     *
     * @var string
     */
    protected $listProductBlock = '.products.wrapper.grid';

    /**
     * MAP popup
     *
     * @var string
     */
    protected $mapBlock = '#map-popup-content';

    /**
     * Layered navigation block
     *
     * @var string
     */
    protected $layeredNavigationBlock = '.block.filter';

    /**
     * Custom constructor
     */
    protected function _init()
    {
        $this->_url = $_ENV['app_frontend_url'] . self::MCA;
    }

    /**
     * Get product list block
     *
     * @return \Magento\Catalog\Test\Block\Product\ListProduct
     */
    public function getListProductBlock()
    {
        return Factory::getBlockFactory()->getMagentoCatalogProductListProduct(
            $this->_browser->find($this->listProductBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Get product price block
     *
     * @return \Magento\Catalog\Test\Block\Product\Price
     */
    public function getMapBlock()
    {
        return Factory::getBlockFactory()->getMagentoCatalogProductPrice(
            $this->_browser->find($this->mapBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Get layered navigation block
     *
     * @return \Magento\Search\Test\Block\Catalog\Layer\View
     */
    public function getLayeredNavigationBlock()
    {
        return Factory::getBlockFactory()->getMagentoSearchCatalogLayerView(
            $this->_browser->find($this->layeredNavigationBlock, Locator::SELECTOR_CSS)
        );
    }
}
