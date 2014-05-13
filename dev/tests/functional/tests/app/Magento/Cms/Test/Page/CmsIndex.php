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

namespace Magento\Cms\Test\Page;

use Mtf\Page\Page;
use Mtf\Factory\Factory;
use Mtf\Client\Element\Locator;

/**
 * Class CmsIndex
 * Home page for frontend
 *
 */
class CmsIndex extends Page
{
    /**
     * URL for home page
     */
    const MCA = 'cms/index/index';

    /**
     * Search block
     *
     * @var string
     */
    protected $searchBlock = '#search_mini_form';

    /**
     * Top menu navigation block
     *
     * @var string
     */
    protected $topmenuBlock = '[role=navigation]';

    /**
     * Page title block
     *
     * @var string
     */
    protected $titleBlock = '.page.title';

    /**
     * Footer block
     *
     * @var string
     */
    protected $footerBlock = 'footer.footer';

    /**
     * Page Top Links block
     *
     * @var string
     */
    protected $linksBlock = '.header .links';

    /**
     * Store switcher block path
     */
    private $storeSwitcherBlock = '//*[@data-ui-id="language-switcher"]';

    /**
     * Custom constructor
     */
    protected function _init()
    {
        $this->_url = $_ENV['app_frontend_url'];
    }

    /**
     * Get the search block
     *
     * @return \Magento\Catalog\Test\Block\Search
     */
    public function getSearchBlock()
    {
        return Factory::getBlockFactory()->getMagentoCatalogSearch(
            $this->_browser->find($this->searchBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Get category title block
     *
     * @return \Magento\Theme\Test\Block\Html\Topmenu
     */
    public function getTopmenu()
    {
        return Factory::getBlockFactory()->getMagentoThemeHtmlTopmenu(
            $this->_browser->find($this->topmenuBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Get title block
     *
     * @return \Magento\Theme\Test\Block\Html\Title
     */
    public function getTitleBlock()
    {
        return Factory::getBlockFactory()->getMagentoThemeHtmlTitle(
            $this->_browser->find($this->titleBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Get footer block
     *
     * @return \Magento\Theme\Test\Block\Html\Footer
     */
    public function getFooterBlock()
    {
        return Factory::getBlockFactory()->getMagentoThemeHtmlFooter(
            $this->_browser->find($this->footerBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Get Top Links block
     *
     * @return \Magento\Theme\Test\Block\Links
     */
    public function getLinksBlock()
    {
        return Factory::getBlockFactory()->getMagentoThemeLinks(
            $this->_browser->find($this->linksBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Get store switcher
     *
     * @return \Magento\Store\Test\Block\Switcher
     */
    public function getStoreSwitcherBlock()
    {
        return Factory::getBlockFactory()->getMagentoStoreSwitcher(
            $this->_browser->find($this->storeSwitcherBlock, Locator::SELECTOR_XPATH)
        );
    }
}
