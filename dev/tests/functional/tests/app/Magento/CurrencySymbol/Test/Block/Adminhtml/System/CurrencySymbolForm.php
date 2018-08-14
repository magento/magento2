<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CurrencySymbol\Test\Block\Adminhtml\System;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Class CurrencySymbolForm
 * Currency Symbol form
 */
class CurrencySymbolForm extends Form
{
    /**
     * Custom Currency locator
     *
     * @var string
     */
    protected $currencyRow = '//div[input[@id="custom_currency_symbol%s"]]';

    /**
     * Fill the root form
     *
     * @param FixtureInterface $fixture
     * @param SimpleElement|null $element
     * @return $this
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null)
    {
        $element = $this->_rootElement->find(sprintf($this->currencyRow, $fixture->getCode()), Locator::SELECTOR_XPATH);
        $data = $fixture->getData();
        unset($data['code']);
        $mapping = $this->dataMapping($data);
        $this->_fill($mapping, $element);
        return $this;
    }
}
