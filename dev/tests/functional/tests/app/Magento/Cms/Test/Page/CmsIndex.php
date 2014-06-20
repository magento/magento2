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

use Mtf\Page\FrontendPage;

/**
 * Class CmsIndex
 */
class CmsIndex extends FrontendPage
{
    const MCA = 'cms/index/index';

    protected $_blocks = [
        'searchBlock' => [
            'name' => 'searchBlock',
            'class' => 'Magento\Catalog\Test\Block\Search',
            'locator' => '#search_mini_form',
            'strategy' => 'css selector',
        ],
        'topmenu' => [
            'name' => 'topmenu',
            'class' => 'Magento\Theme\Test\Block\Html\Topmenu',
            'locator' => '[role=navigation]',
            'strategy' => 'css selector',
        ],
        'titleBlock' => [
            'name' => 'titleBlock',
            'class' => 'Magento\Theme\Test\Block\Html\Title',
            'locator' => '.page.title',
            'strategy' => 'css selector',
        ],
        'footerBlock' => [
            'name' => 'footerBlock',
            'class' => 'Magento\Theme\Test\Block\Html\Footer',
            'locator' => 'footer.footer',
            'strategy' => 'css selector',
        ],
        'linksBlock' => [
            'name' => 'linksBlock',
            'class' => 'Magento\Theme\Test\Block\Links',
            'locator' => '.header .links',
            'strategy' => 'css selector',
        ],
        'storeSwitcherBlock' => [
            'name' => 'storeSwitcherBlock',
            'class' => 'Magento\Store\Test\Block\Switcher',
            'locator' => '[data-ui-id="language-switcher"]',
            'strategy' => 'css selector',
        ],
        'cartSidebarBlock' => [
            'name' => 'cartSidebarBlock',
            'class' => 'Magento\Checkout\Test\Block\Cart\Sidebar',
            'locator' => '[data-block="minicart"]',
            'strategy' => 'css selector',
        ],
    ];

    /**
     * @return \Magento\Catalog\Test\Block\Search
     */
    public function getSearchBlock()
    {
        return $this->getBlockInstance('searchBlock');
    }

    /**
     * @return \Magento\Theme\Test\Block\Html\Topmenu
     */
    public function getTopmenu()
    {
        return $this->getBlockInstance('topmenu');
    }

    /**
     * @return \Magento\Theme\Test\Block\Html\Title
     */
    public function getTitleBlock()
    {
        return $this->getBlockInstance('titleBlock');
    }

    /**
     * @return \Magento\Theme\Test\Block\Html\Footer
     */
    public function getFooterBlock()
    {
        return $this->getBlockInstance('footerBlock');
    }

    /**
     * @return \Magento\Theme\Test\Block\Links
     */
    public function getLinksBlock()
    {
        return $this->getBlockInstance('linksBlock');
    }

    /**
     * @return \Magento\Store\Test\Block\Switcher
     */
    public function getStoreSwitcherBlock()
    {
        return $this->getBlockInstance('storeSwitcherBlock');
    }

    /**
     * @return \Magento\Checkout\Test\Block\Cart\Sidebar
     */
    public function getCartSidebarBlock()
    {
        return $this->getBlockInstance('cartSidebarBlock');
    }
}
