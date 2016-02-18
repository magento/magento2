<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * SynonymGroup Page block for the content on the frontend.
 */
class Page extends Block
{
    /**
     * Selector for initial script.
     *
     * @var string
     */
    protected $initialScript = 'script[type="text/x-magento-init"]';

    /**
     * Synonym Group page content class.
     *
     * @var string
     */
    protected $synonymGroupPageContentClass = ".column.main";

    /**
     * Synonym Group page title.
     *
     * @var string
     */
    protected $synonymGroupPageTitle = ".page-title-wrapper";

    /**
     * Synonym Group page text locator.
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
        'Synonym Group page Link' => './/*/a[contains(.,"%s")]',
    ];

    /**
     * Get page content text.
     *
     * @return string
     */
    public function getPageContent()
    {
        return $this->_rootElement->find($this->synonymGroupPageContentClass)->getText();
    }

    /**
     * Get page title.
     *
     * @return string
     */
    public function getPageTitle()
    {
        return $this->_rootElement->find($this->synonymGroupPageTitle)->getText();
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
        $this->_rootElement->waitUntil(
            function () use ($text) {
                return $this->browser->find($text, Locator::SELECTOR_XPATH)->isVisible() == true ? false : null;
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
            return $this->_rootElement
                ->find(sprintf($this->widgetSelectors[$widgetType], $widgetText), Locator::SELECTOR_XPATH)
                ->isVisible();
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
        $this->waitForElementNotVisible($this->initialScript);
        sleep(3); // TODO: remove after resolving an issue with ajax on Frontend.
    }
}
