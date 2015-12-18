<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Block\Widget;

use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Ui\Test\Block\Adminhtml\AbstractFormContainers;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\ElementInterface;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Is used to represent any form with tabs on the page.
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FormTabs extends AbstractFormContainers
{
    /**
     * List of tabs
     *
     * @return array
     */
    public function getTabs()
    {
        return $this->containers;
    }

    /**
     * Get tab class.
     *
     * @param string $tabName
     * @return Tab
     * @throws \Exception
     */
    public function getTab($tabName)
    {
        return $this->getContainer($tabName);
    }

    /**
     * @param array $tabsData
     * @param SimpleElement $element
     * @return $this
     */
    protected function fillTabs($tabsData, $element)
    {
        return $this->fillContainers($tabsData, $element);
    }

    /**
     * Update form with tabs.
     *
     * @param FixtureInterface $fixture
     * @return FormTabs
     */
    public function update(FixtureInterface $fixture)
    {
        $tabs = $this->getFieldsByTabs($fixture);
        foreach ($tabs as $tab => $tabFields) {
            $this->openTab($tab)->setFieldsData($tabFields, $this->_rootElement);
        }
        return $this;
    }

    /**
     * Create data array for filling tabs.
     *
     * @param FixtureInterface $fixture
     * @return array
     */
    protected function getFieldsByTabs(FixtureInterface $fixture)
    {
        if ($fixture instanceof InjectableFixture) {
            $tabs = $this->getFixtureFieldsByContainers($fixture);
        } else {
            $tabs = $this->getFixtureFieldsByTabsDeprecated($fixture);
        }
        return $tabs;
    }

    /**
     * Create data array for filling tabs (deprecated fixture specification).
     *
     * @param FixtureInterface $fixture
     * @return array
     * @deprecated
     */
    private function getFixtureFieldsByTabsDeprecated(FixtureInterface $fixture)
    {
        $tabs = [];

        $dataSet = $fixture->getData();
        $fields = isset($dataSet['fields']) ? $dataSet['fields'] : [];

        foreach ($fields as $field => $attributes) {
            if (array_key_exists('group', $attributes) && $attributes['group'] !== null) {
                $tabs[$attributes['group']][$field] = $attributes;
            } elseif (!array_key_exists('group', $attributes)) {
                $this->unassignedFields[$field] = $attributes;
            }
        }
        return $tabs;
    }

    /**
     * @param string $tabName
     * @return $this
     */
    protected function openContainer($tabName)
    {
        return $this->openTab($tabName);
    }

    /**
     * Open tab.
     *
     * @param string $tabName
     * @return Tab
     */
    public function openTab($tabName)
    {
        $this->browser->find($this->header)->hover();
        $this->getTabElement($tabName)->click();
        return $this;
    }

    /**
     * Get tab element.
     *
     * @param string $tabName
     * @return ElementInterface
     */
    protected function getTabElement($tabName)
    {
        $selector = $this->containers[$tabName]['selector'];
        $strategy = isset($this->containers[$tabName]['strategy'])
            ? $this->containers[$tabName]['strategy']
            : Locator::SELECTOR_CSS;
        return $this->_rootElement->find($selector, $strategy);
    }

    /**
     * @param string $tabName
     * @return bool
     */
    protected function isContainerVisible($tabName)
    {
        return $this->isTabVisible($tabName);
    }

    /**
     * Check whether tab is visible.
     *
     * @param string $tabName
     * @return bool
     */
    public function isTabVisible($tabName)
    {
        return $this->getTabElement($tabName)->isVisible();
    }
}
