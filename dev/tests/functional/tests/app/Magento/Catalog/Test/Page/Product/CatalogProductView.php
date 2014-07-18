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

namespace Magento\Catalog\Test\Page\Product;

use Mtf\Page\FrontendPage;
use Mtf\Fixture\FixtureInterface;

/**
 * Class CatalogProductView
 *
 * Frontend product view page
 */
class CatalogProductView extends FrontendPage
{
    /**
     * URL for catalog product grid
     */
    const MCA = 'catalog/product/view';

    protected $_blocks = [
        'viewBlock' => [
            'name' => 'viewBlock',
            'class' => 'Magento\Catalog\Test\Block\Product\View',
            'locator' => '#maincontent',
            'strategy' => 'css selector',
        ],
        'customOptionsBlock' => [
            'name' => 'customOptionsBlock',
            'class' => 'Magento\Catalog\Test\Block\Product\View\CustomOptions',
            'locator' => '#product-options-wrapper',
            'strategy' => 'css selector',
        ],
        'relatedProductBlock' => [
            'name' => 'relatedProductBlock',
            'class' => 'Magento\Catalog\Test\Block\Product\ProductList\Related',
            'locator' => '.block.related',
            'strategy' => 'css selector',
        ],
        'upsellBlock' => [
            'name' => 'upsellBlock',
            'class' => 'Magento\Catalog\Test\Block\Product\ProductList\Upsell',
            'locator' => '.block.upsell',
            'strategy' => 'css selector',
        ],
        'crosssellBlock' => [
            'name' => 'crosssellBlock',
            'class' => 'Magento\Catalog\Test\Block\Product\ProductList\Crosssell',
            'locator' => '.block.crosssell',
            'strategy' => 'css selector',
        ],
        'messagesBlock' => [
            'name' => 'messagesBlock',
            'class' => 'Magento\Core\Test\Block\Messages',
            'locator' => '.page.messages .messages',
            'strategy' => 'css selector',
        ],
        'reviewSummary' => [
            'name' => 'reviewSummary',
            'class' => 'Magento\Review\Test\Block\Product\View\Summary',
            'locator' => '.product.reviews.summary',
            'strategy' => 'css selector',
        ],
        'reviewFormBlock' => [
            'name' => 'reviewFormBlock',
            'class' => 'Magento\Review\Test\Block\Form',
            'locator' => '#review-form',
            'strategy' => 'css selector',
        ],
        'customerReviewBlock' => [
            'name' => 'customerReviewBlock',
            'class' => 'Magento\Review\Test\Block\Product\View',
            'locator' => '#customer-reviews',
            'strategy' => 'css selector',
        ],
        'mapBlock' => [
            'name' => 'mapBlock',
            'class' => 'Magento\Catalog\Test\Block\Product\Price',
            'locator' => '#map-popup-click-for-price',
            'strategy' => 'css selector',
        ],
        'titleBlock' => [
            'name' => 'titleBlock',
            'class' => 'Magento\Theme\Test\Block\Html\Title',
            'locator' => '.page.title',
            'strategy' => 'css selector',
        ]
    ];

    /**
     * Custom constructor
     */
    protected function _init()
    {
        $this->_url = $_ENV['app_frontend_url'] . self::MCA;
    }

    /**
     * Page initialization
     *
     * @param FixtureInterface $fixture
     * @return void
     */
    public function init(FixtureInterface $fixture)
    {
        $this->_url = $_ENV['app_frontend_url'] . $fixture->getUrlKey() . '.html';
    }

    /**
     * Get product view block
     *
     * @return \Magento\Catalog\Test\Block\Product\View
     */
    public function getViewBlock()
    {
        return $this->getBlockInstance('viewBlock');
    }

    /**
     * Get product options block
     *
     * @return \Magento\Catalog\Test\Block\Product\View\CustomOptions
     */
    public function getCustomOptionsBlock()
    {
        return $this->getBlockInstance('customOptionsBlock');
    }

    /**
     * @return \Magento\Catalog\Test\Block\Product\ProductList\Related
     */
    public function getRelatedProductBlock()
    {
        return $this->getBlockInstance('relatedProductBlock');
    }

    /**
     * @return \Magento\Review\Test\Block\Form
     */
    public function getReviewFormBlock()
    {
        return $this->getBlockInstance('reviewFormBlock');
    }

    /**
     * @return \Magento\Review\Test\Block\Product\View
     */
    public function getCustomerReviewBlock()
    {
        return $this->getBlockInstance('customerReviewBlock');
    }

    /**
     * @return \Magento\Core\Test\Block\Messages
     */
    public function getMessagesBlock()
    {
        return $this->getBlockInstance('messagesBlock');
    }

    /**
     * @return \Magento\Review\Test\Block\Product\View\Summary
     */
    public function getReviewSummaryBlock()
    {
        return $this->getBlockInstance('reviewSummary');
    }

    /**
     * @return \Magento\Catalog\Test\Block\Product\ProductList\Upsell
     */
    public function getUpsellBlock()
    {
        return $this->getBlockInstance('upsellBlock');
    }

    /**
     * @return \Magento\Catalog\Test\Block\Product\ProductList\Crosssell
     */
    public function getCrosssellBlock()
    {
        return $this->getBlockInstance('crosssellBlock');
    }

    /**
     * @return \Magento\Catalog\Test\Block\Product\Price
     */
    public function getMapBlock()
    {
        return $this->getBlockInstance('mapBlock');
    }

    /**
     * @return \Magento\Theme\Test\Block\Html\Title
     */
    public function getTitleBlock()
    {
        return $this->getBlockInstance('titleBlock');
    }
}
