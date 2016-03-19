<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Attribute\Edit;

use Magento\Backend\Test\Block\Widget\Tab;
use Magento\Backend\Test\Block\Widget\FormTabs;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Element;
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
    protected $closedToggle = '.admin__collapsible-block-wrapper:not(.opened) [data-toggle="collapse"]';

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
                    $tabData = $this->getTab($tabName)->getDataFormTab();
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
                    $tabData = $this->getTab($tabName)->getDataFormTab($fields, $this->_rootElement);
                    $data = array_merge($data, $tabData);
                }
            }
        }

        return $this->removeEmptyValues($data);
    }

    /**
     * Remove recursive all empty values in array.
     *
     * @param mixed $input
     * @return mixed
     */
    protected function removeEmptyValues($input)
    {
        if (!is_array($input)) {
            return $input;
        }
        $filteredArray = [];
        foreach ($input as $key => $value) {
            if ($value) {
                $filteredArray[$key] = $this->removeEmptyValues($value);
            }
        }

        return $filteredArray;
    }

    /**
     * Expand all toggles.
     *
     * @return void
     */
    protected function expandAllToggles()
    {
        $closedToggles = $this->_rootElement->getElements($this->closedToggle);
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
}
