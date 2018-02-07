<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Page Top Links block.
 */
class Links extends Block
{
    /**
     * Locator value for correspondent link.
     *
     * @var string
     */
    protected $link = '//a[contains(text(), "%s")]';

    /**
     * Locator value for Authorization link.
     *
     * @var string
     */
    protected $authorizationLink = '.authorization-link a';

    /**
     * Locator value for welcome message.
     *
     * @var string
     */
    protected $welcomeMessage = '.greet.welcome';

    /**
     * Locator value for "Expand/Collapse Customer Menu" button.
     *
     * @var string
     */
    protected $toggleButton = '[data-action="customer-menu-toggle"]';

    /**
     * Locator value for Customer Menu.
     *
     * @var string
     */
    protected $customerMenu = '.customer-menu > ul';

    /**
     * Expand Customer Menu (located in page Header) if it was collapsed.
     *
     * @return void
     */
    protected function expandCustomerMenu()
    {
        $this->_rootElement->find($this->toggleButton)->click();
    }

    /**
     * Open link by its title.
     *
     * @param string $linkTitle
     * @return void
     */
    public function openLink($linkTitle)
    {
        $link = $this->_rootElement->find(sprintf($this->link, $linkTitle), Locator::SELECTOR_XPATH);
        if (!$link->isVisible()) {
            $this->expandCustomerMenu();
        }
        $link->click();
    }

    /**
     * Verify if correspondent link is present or not.
     *
     * @param string $linkTitle
     * @return bool
     */
    public function isLinkVisible($linkTitle)
    {
        $link = $this->_rootElement->find(sprintf($this->link, $linkTitle), Locator::SELECTOR_XPATH);
        if (!$link->isVisible()) {
            $this->expandCustomerMenu();
        }
        return $link->isVisible();
    }

    /**
     * Wait until correspondent link appears.
     *
     * @param string $linkTitle
     * @return void
     */
    public function waitLinkIsVisible($linkTitle)
    {
        $browser = $this->_rootElement;
        $selector = sprintf($this->link, $linkTitle);
        $browser->waitUntil(
            function () use ($browser, $selector) {
                $element = $browser->find($selector, Locator::SELECTOR_XPATH);
                return $element->isVisible() ? true : null;
            }
        );
    }

    /**
     * Get url from link.
     *
     * @param string $linkTitle
     * @return string
     */
    public function getLinkUrl($linkTitle)
    {
        $link = $this->_rootElement->find(sprintf($this->link, $linkTitle), Locator::SELECTOR_XPATH)
            ->getAttribute('href');

        return trim($link);
    }

    /**
     * Wait until welcome message appears.
     *
     * @return void
     */
    public function waitWelcomeMessage()
    {
        $this->waitForElementVisible($this->welcomeMessage);
    }

    /**
     * Verify if authorization link is present or not.
     *
     * @return bool
     */
    public function isAuthorizationVisible()
    {
        return $this->_rootElement->find($this->authorizationLink)->isVisible();
    }
}
