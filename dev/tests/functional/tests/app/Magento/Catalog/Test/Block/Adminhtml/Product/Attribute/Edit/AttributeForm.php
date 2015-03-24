<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Attribute\Edit;

use Magento\Backend\Test\Block\Widget\Tab;
use Magento\Backend\Test\Block\Widget\FormTabs;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Element;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Catalog Product Attribute form.
 */
class AttributeForm extends FormTabs
{
    /**
     * Closed toggles selector.
     *
     * @var string
     */
    protected $closedToggle = '//*[contains(@class,"collapsable-wrapper") and not(contains(@class,"opened"))]//strong';

    /**
     * Properties tab selector.
     *
     * @var string
     */
    protected $propertiesTab = '#product_attribute_tabs_main';

    /**
     * Page title.
     *
     * @var string
     */
    protected $pageTitle = '.page-title';

    /**
     * Get data of the tabs.
     *
     * @param FixtureInterface $fixture
     * @param SimpleElement $element
     * @return array
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData(FixtureInterface $fixture = null, SimpleElement $element = null)
    {
        $this->waitForElementVisible($this->propertiesTab);
        $data = [];
        if (null === $fixture) {
            foreach ($this->tabs as $tabName => $tab) {
                if ($this->isTabVisible($tabName)) {
                    $this->openTab($tabName);
                    $this->expandAllToggles();
                    $tabData = $this->getTabElement($tabName)->getDataFormTab();
                    $data = array_merge($data, $tabData);
                }
            }
        } else {
            $isHasData = ($fixture instanceof InjectableFixture) ? $fixture->hasData() : true;
            $tabsFields = $isHasData ? $this->getFieldsByTabs($fixture) : [];
            foreach ($tabsFields as $tabName => $fields) {
                if ($this->isTabVisible($tabName)) {
                    $this->openTab($tabName);
                    $this->expandAllToggles();
                    $tabData = $this->getTabElement($tabName)->getDataFormTab($fields, $this->_rootElement);
                    $data = array_merge($data, $tabData);
                }
            }
        }

        return $data;
    }

    /**
     * Expand all toggles.
     *
     * @return void
     */
    protected function expandAllToggles()
    {
        $closedToggles = $this->_rootElement->getElements($this->closedToggle, Locator::SELECTOR_XPATH);
        foreach ($closedToggles as $toggle) {
            $toggle->click();
        }
    }

    /**
     * Open tab.
     *
     * @param string $tabName
     * @return Tab
     */
    public function openTab($tabName)
    {
        $this->browser->find($this->pageTitle)->click(); // Handle menu overlap problem
        return parent::openTab($tabName);
    }

    /**
     * Check if tab is visible.
     *
     * @param string $tabName
     * @return bool
     */
    protected function isTabVisible($tabName)
    {
        $selector = $this->tabs[$tabName]['selector'];
        $strategy = isset($this->tabs[$tabName]['strategy'])
            ? $this->tabs[$tabName]['strategy']
            : Locator::SELECTOR_CSS;
        return $this->_rootElement->find($selector, $strategy)->isVisible();
    }
}
