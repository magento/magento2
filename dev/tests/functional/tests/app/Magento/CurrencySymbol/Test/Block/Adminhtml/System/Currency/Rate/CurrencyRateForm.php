<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CurrencySymbol\Test\Block\Adminhtml\System\Currency\Rate;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Currency Rate form.
 */
class CurrencyRateForm extends Form
{
    /**
     * Locator value for "Messages" block.
     *
     * @var string
     */
    protected $message = '#messages';

    /**
     * Locator value for "Import" button.
     *
     * @var string
     */
    protected $importButton = '[data-ui-id$="import-button"]';

    /**
     * Locator value for "[USD][UAH] Rate" text field.
     *
     * @var string
     */
    protected $USDUAHRate = '[name$="rate[USD][UAH]"]';

    /**
     * Click on the "Import" button.
     *
     * @throws \Exception
     * @return void
     */
    public function clickImportButton()
    {
        $this->_rootElement->find($this->importButton)->click();

        //Wait message
        $browser = $this->browser;
        $selector = $this->message;
        $browser->waitUntil(
            function () use ($browser, $selector) {
                $message = $browser->find($selector);
                return $message->isVisible() ? true : null;
            }
        );
    }

    /*
     * Populate USD-UAH rate value.
     *
     * @throws \Exception
     * @return void
     */
    public function fillCurrencyUSDUAHRate()
    {
        $this->_rootElement->find($this->USDUAHRate)->setValue('2.000');

        //Wait message
        $browser = $this->browser;
        $selector = $this->message;
        $browser->waitUntil(
            function () use ($browser, $selector) {
                $message = $browser->find($selector);
                return $message->isVisible() ? true : null;
            }
        );
    }

    /**
     * Fill "Currency Rates" form.
     *
     * @param FixtureInterface $fixture
     * @param SimpleElement|null $element
     * @return $this
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null)
    {
        /** @var \Magento\Directory\Test\Fixture\CurrencyRate $fixture */
        $this->placeholders['currency_from'] = $fixture->getCurrencyFrom();
        $this->placeholders['currency_to'] = $fixture->getCurrencyTo();
        $this->applyPlaceholders();

        return parent::fill($fixture, $element);
    }
}
