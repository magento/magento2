<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Upgrade\Test\Block;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Perform Authentication block.
 */
class Authentication extends Form
{
    /**
     * 'Save Config' button.
     *
     * @var string
     */
    protected $save = "[ng-click*='saveAuthJson']";

    /**
     * First field selector
     *
     * @var string
     */
    protected $firstField = '[name="username"]';

    /**
     * Click on 'Save Config' button.
     *
     * @return void
     */
    public function clickSaveConfig()
    {
        $this->_rootElement->find($this->save, Locator::SELECTOR_CSS)->click();
    }

    /**
     * Ensure the form is loaded and fill the root form
     *
     * @param FixtureInterface $fixture
     * @param SimpleElement|null $element
     * @return $this
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null)
    {
        $this->waitForElementVisible($this->firstField);
        return parent::fill($fixture, $element);
    }
}
