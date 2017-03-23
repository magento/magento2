<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Test\Block\Currency;

use Magento\CurrencySymbol\Test\Fixture\CurrencySymbolEntity;
use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Switcher Currency Symbol.
 */
class Switcher extends Block
{
    /**
     * Currency switch locator.
     *
     * @var string
     */
    protected $currencySwitch = '#switcher-currency-trigger';

    /**
     * Currency link locator.
     *
     * @var string
     */
    protected $currencyLinkLocator = '//li[@class="currency-%s switcher-option"]//a';

    /**
     * Language type css selector.
     *
     * @var string
     */
    protected $language = '.language-';

    /**
     * Switch currency to specified one.
     *
     * @param CurrencySymbolEntity $currencySymbol
     * @return void
     */
    public function switchCurrency(CurrencySymbolEntity $currencySymbol)
    {
        $this->waitForElementVisible($this->currencySwitch);
        $currencyLink = $this->_rootElement->find($this->currencySwitch);
        $customCurrencySwitch = explode(" ", $this->_rootElement->find($this->currencySwitch)->getText());
        $currencyCode = $currencySymbol->getCode();
        if ($customCurrencySwitch[0] !== $currencyCode) {
            $currencyLink->click();
            $currencyLink = $this->_rootElement
                ->find(sprintf($this->currencyLinkLocator, $currencyCode), Locator::SELECTOR_XPATH);
            $currencyLink->click();
            $this->waitForElementVisible($this->language . $currencyCode);
        }
    }
}
