<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * Fill currency rate form.
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
