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
     * Selector for input.
     *
     * @var string
     */
    protected $inputRateSelector = 'input[name="rate[%s][%s]"]';

    /**
     * Fill currency rate form.
     *
     * @param FixtureInterface $fixture
     * @param SimpleElement|null $element
     * @return $this
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null)
    {
        $data = $fixture->getData();
        $inputRateSelector = sprintf($this->inputRateSelector, $data['currency_from'], $data['currency_to']);
        $this->_rootElement->find($inputRateSelector)->setValue($data['rate']);

        return $this;
    }
}
