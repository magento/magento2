<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Cms Page block for the content on the frontend.
 */
class Page extends Block
{
    /**
     * Selector for uninitialized page.
     *
     * @var string
     */
    protected $uninitialized = '//body[(@data-mage-init) or (@aria-busy="true")]';

    /**
     * Cms page content class.
     *
     * @var string
     */
    protected $cmsPageContentClass = ".column.main";

    /**
     * Cms page title.
     *
     * @var string
     */
    protected $cmsPageTitle = ".page-title-wrapper";

    /**
     * Cms page text locator.
     *
     * @var string
     */
    protected $textSelector = "//div[contains(.,'%s')]";

    /**
     * Widgets selectors.
     *
     * @var array
     */
    protected $widgetSelectors = [
        'CMS Page Link' => './/*/a[contains(.,"%s")]',
        'Catalog Category Link' => './/*/a[contains(.,"%s")]',
        'Catalog Product Link' => './/*/a[contains(.,"%s")]',
        'Recently Compared Products' => './/*/div[contains(@class,"block widget compared grid") and contains(.,"%s")]',
        'Recently Viewed Products' => './/*/div[contains(@class,"block widget viewed grid") and contains(.,"%s")]',
        'Catalog New Products List' => './/*/div[contains(@class,"widget new") and contains(.,"%s")]',
        'CMS Static Block' => './/*/div[contains(@class,"widget static block") and contains(.,"%s")]',
    ];

    /**
     * Get page content text.
     *
     * @return string
     */
    public function getPageContent()
    {
        return $this->_rootElement->find($this->cmsPageContentClass)->getText();
    }

    /**
     * Get page title.
     *
     * @return string
     */
    public function getPageTitle()
    {
        return $this->_rootElement->find($this->cmsPageTitle)->getText();
    }

    /**
     * Wait for text is visible in the block.
     *
     * @param string $text
     * @return void
     */
    public function waitUntilTextIsVisible($text)
    {
        $text = sprintf($this->textSelector, $text);
        $browser = $this->browser;
        $this->_rootElement->waitUntil(
            function () use ($browser, $text) {
                $blockText = $browser->find($text, Locator::SELECTOR_XPATH);
                return $blockText->isVisible() == true ? false : null;
            }
        );
    }

    /**
     * Check is visible widget selector.
     *
     * @param string $widgetType
     * @param string $widgetText
     * @return bool
     * @throws \Exception
     */
    public function isWidgetVisible($widgetType, $widgetText)
    {
        if (isset($this->widgetSelectors[$widgetType])) {
            return $this->_rootElement->find(
                sprintf($this->widgetSelectors[$widgetType], $widgetText),
                Locator::SELECTOR_XPATH
            )->isVisible();
        } else {
            throw new \Exception('Determine how to find the widget on the page.');
        }
    }

    /**
     * Waiting page initialization.
     *
     * @return void
     */
    public function waitPageInit()
    {
        $browser = $this->browser;
        $uninitialized = $this->uninitialized;

        $this->_rootElement->waitUntil(
            function () use ($browser, $uninitialized) {
                return $browser->find($uninitialized, Locator::SELECTOR_XPATH)->isVisible() == false ? true : null;
            }
        );
    }
}
