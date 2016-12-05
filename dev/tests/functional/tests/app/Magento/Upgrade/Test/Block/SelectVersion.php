<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Upgrade\Test\Block;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Select version block.
 */
class SelectVersion extends Form
{
    /**
     * 'Next' button.
     *
     * @var string
     */
    protected $next = "[ng-click*='update']";

    /**
     * First field selector
     *
     * @var string
     */
    protected $firstField = '#selectVersion';

    /**
     * Other components loader selector
     *
     * @var string
     */
    private $loader = 'div[ng-show="updateComponents.yes && !upgradeProcessError"] > div.message.message-spinner';

    /**
     * Click on 'Next' button.
     *
     * @return void
     */
    public function clickNext()
    {
        $this->_rootElement->find($this->next, Locator::SELECTOR_CSS)->click();
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

    /**
     * Choose 'yes' for upgrade option called 'Other components'
     *
     * @return void
     */
    public function chooseUpgradeOtherComponents()
    {
        $this->_rootElement->find("[for=yesUpdateComponents]", Locator::SELECTOR_CSS)->click();
        $this->waitForElementVisible($this->loader);
        $this->waitForElementNotVisible($this->loader);
    }
}
