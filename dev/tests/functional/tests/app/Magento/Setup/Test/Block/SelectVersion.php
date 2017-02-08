<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Block;

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
     * Show all versions checkbox
     *
     * @var string
     */
    private $showAllVersions = '#showUnstable';

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
        $this->chooseShowAllVersions();

        return parent::fill($fixture, $element);
    }

    /**
     * Show all versions include unstable
     *
     * @return void
     */
    private function chooseShowAllVersions()
    {
        $element = $this->_rootElement->find($this->showAllVersions, Locator::SELECTOR_CSS);
        if ($element->isVisible()) {
            $element->click();
        }
    }

    /**
     * Choose 'yes' for upgrade option called 'Other components'
     *
     * @return void
     */
    public function chooseUpgradeOtherComponents()
    {
        $this->_rootElement->find("[for=yesUpdateComponents]", Locator::SELECTOR_CSS)->click();
        $this->waitForElementVisible("[ng-show='componentsProcessed']");
    }
}
