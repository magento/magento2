<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Block;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Setup\Test\Block\SelectVersion\OtherComponentsGrid;

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
     * CSS selector for Other Components Grid Block.
     *
     * @var string
     */
    private $otherComponentsGrid = '.admin__data-grid-wrap[ng-show="componentsProcessed"]';

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
     * Choose 'yes' for upgrade option called 'Other components'.
     *
     * @param array $packages
     * @return void
     */
    public function chooseUpgradeOtherComponents(array $packages)
    {
        $this->_rootElement->find("[for=yesUpdateComponents]")->click();
        $this->waitForElementNotVisible("[ng-show=\"!componentsProcessed\"");
        $this->getOtherComponentsGrid()->setVersions($packages);
    }

    /**
     * Get grid block for other components.
     *
     * @return OtherComponentsGrid
     */
    private function getOtherComponentsGrid()
    {
        return $this->blockFactory->create(
            OtherComponentsGrid::class,
            ['element' => $this->_rootElement->find($this->otherComponentsGrid)]
        );
    }
}
