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

use Mtf\Page\Page;
use Mtf\Factory\Factory;
use Mtf\Fixture\FixtureInterface;
use Mtf\Client\Element\Locator;
use Magento\GiftCard\Test\Block\Catalog\Product\View\Type\GiftCard;
use Magento\Catalog\Test\Block\Product\ProductList\Crosssell;
use Magento\Catalog\Test\Block\Product\Price;
use Magento\Catalog\Test\Block\Product\ProductList\Related;
use Magento\Catalog\Test\Block\Product\ProductList\Upsell;
use Magento\Catalog\Test\Block\Product\View;
use Magento\Catalog\Test\Block\Product\View\Options;
use Magento\Catalog\Test\Block\Product\View\CustomOptions;
use Magento\Downloadable\Test\Block\Catalog\Product\Links;
use Magento\Review\Test\Block\Product\View\Summary;
use Magento\Review\Test\Block\Form;
use Magento\Review\Test\Block\Product\View as ReviewView;
use Magento\Core\Test\Block\Messages;

/**
 * Class CatalogProductView
 * Frontend product view page
 *
 */
class CatalogProductView extends Page
{
    /**
     * URL for catalog product grid
     */
    const MCA = 'catalog/product/view';

    /**
     * Review summary selector
     *
     * @var string
     */
    protected $reviewSummarySelector = '.product.reviews.summary';

    /**
     * Review form
     *
     * @var string
     */
    protected $reviewFormBlock = '#review-form';

    /**
     * Customer reviews block
     *
     * @var string
     */
    protected $customerReviewBlock = '#customer-reviews';

    /**
     * Messages selector
     *
     * @var string
     */
    protected $messagesSelector = '.page.messages .messages';

    /**
     * Product View block
     *
     * @var string
     */
    protected $viewBlock = '.column.main';

    /**
     * Product options block
     *
     * @var string
     */
    protected $optionsBlock = '#product-options-wrapper';

    /**
     * Related product selector
     *
     * @var string
     */
    protected $relatedProductSelector = '.block.related';

    /**
     * Upsell selector
     *
     * @var string
     */
    protected $upsellSelector = '.block.upsell';

    /**
     * Gift Card Block selector
     *
     * @var string
     */
    protected $giftCardBlockSelector = '[data-container-for=giftcard_info]';

    /**
     * Gift Card Amount Block selector
     *
     * @var string
     */
    protected $giftCardBlockAmountSelector = '.fieldset.giftcard.amount';

    /**
     * Cross-sell selector
     *
     * @var string
     */
    protected $crosssellSelector = '.block.crosssell';

    /**
     * @var string
     */
    protected $downloadableLinksSelector = '[data-container-for=downloadable-links]';

    /**
     * MAP popup
     *
     * @var string
     */
    protected $mapBlock = '#map-popup';

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
     */
    public function init(FixtureInterface $fixture)
    {
        $this->_url = $_ENV['app_frontend_url'] . $fixture->getUrlKey() . '.html';
    }

    /**
     * Get product view block
     *
     * @return View
     */
    public function getViewBlock()
    {
        return Factory::getBlockFactory()->getMagentoCatalogProductView(
            $this->_browser->find($this->viewBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Get product options block
     *
     * @return Options
     */
    public function getOptionsBlock()
    {
        return Factory::getBlockFactory()->getMagentoCatalogProductViewOptions(
            $this->_browser->find($this->optionsBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Get product options block
     *
     * @return CustomOptions
     */
    public function getCustomOptionBlock()
    {
        return Factory::getBlockFactory()->getMagentoCatalogProductViewCustomOptions(
            $this->_browser->find('#product-options-wrapper')
        );
    }

    /**
     * Get customer reviews block
     *
     * @return Form
     */
    public function getReviewFormBlock()
    {
        return Factory::getBlockFactory()->getMagentoReviewForm($this->_browser->find($this->reviewFormBlock));
    }

    /**
     * Get customer reviews block
     *
     * @return ReviewView
     */
    public function getCustomerReviewBlock()
    {
        return Factory::getBlockFactory()->getMagentoReviewProductView(
            $this->_browser->find($this->customerReviewBlock)
        );
    }

    /**
     * Get review summary block
     *
     * @return Summary
     */
    public function getReviewSummaryBlock()
    {
        return Factory::getBlockFactory()->getMagentoReviewProductViewSummary(
            $this->_browser->find($this->reviewSummarySelector, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Get upsell block
     *
     * @return Upsell
     */
    public function getUpsellProductBlock()
    {
        return Factory::getBlockFactory()->getMagentoCatalogProductProductListUpsell(
            $this->_browser->find($this->upsellSelector, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Get messages block
     *
     * @return Messages
     */
    public function getMessagesBlock()
    {
        return Factory::getBlockFactory()->getMagentoCoreMessages(
            $this->_browser->find($this->messagesSelector, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Get related product block
     *
     * @return Related
     */
    public function getRelatedProductBlock()
    {
        return Factory::getBlockFactory()->getMagentoCatalogProductProductListRelated(
            $this->_browser->find($this->relatedProductSelector, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Get gift card options block
     *
     * @return GiftCard
     */
    public function getGiftCardBlock()
    {
        return Factory::getBlockFactory()->getMagentoGiftCardCatalogProductViewTypeGiftCard(
            $this->_browser->find($this->giftCardBlockSelector, Locator::SELECTOR_CSS)
        );
    }

    /**
     * @return Links
     */
    public function getDownloadableLinksBlock()
    {
        return Factory::getBlockFactory()->getMagentoDownloadableCatalogProductLinks(
            $this->_browser->find($this->downloadableLinksSelector)
        );
    }

    /**
     * Get product price block
     *
     * @return Price
     */
    public function getMapBlock()
    {
        return Factory::getBlockFactory()->getMagentoCatalogProductPrice(
            $this->_browser->find($this->mapBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Retrieve cross-sell block
     *
     * @return Crosssell
     */
    public function getCrosssellBlock()
    {
        return Factory::getBlockFactory()->getMagentoCatalogProductProductListCrosssell(
            $this->_browser->find($this->crosssellSelector, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Get gift card amount block
     *
     * @return GiftCard
     */
    public function getGiftCardAmountBlock()
    {
        return Factory::getBlockFactory()->getMagentoGiftCardCatalogProductViewTypeGiftCard(
            $this->_browser->find($this->giftCardBlockAmountSelector, Locator::SELECTOR_CSS)
        );
    }
}
