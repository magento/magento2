<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Attribute\Edit;

use Magento\Backend\Test\Block\Widget\FormTabs;
use Magento\Backend\Test\Block\Widget\Tab;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;
use Mtf\Fixture\FixtureInterface;
use Mtf\Fixture\InjectableFixture;

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
     * Get data of the tabs.
     *
     * @param FixtureInterface $fixture
     * @param Element $element
     * @return array
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData(FixtureInterface $fixture = null, Element $element = null)
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
        $closedToggles = $this->_rootElement->find($this->closedToggle, Locator::SELECTOR_XPATH)->getElements();
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
        $selector = $this->tabs[$tabName]['selector'];
        $strategy = isset($this->tabs[$tabName]['strategy'])
            ? $this->tabs[$tabName]['strategy']
            : Locator::SELECTOR_CSS;
        $tab = $this->_rootElement->find($selector, $strategy);
        $target = $this->browser->find('.page-title .title');// Handle menu overlap problem
        $this->_rootElement->dragAndDrop($target);
        $tab->click();
        return $this;
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
