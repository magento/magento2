<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Block\Widget;

use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Ui\Test\Block\Adminhtml\AbstractFormContainers;
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
     * List of tabs.
     *
     * @return array
     */
    public function getTabs()
    {
        return $this->containers;
    }

    /**
     * Get Tab class.
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
     * Fill data into tabs.
     *
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
        $tabs = $this->getFixtureFieldsByContainers($fixture);
        foreach ($tabs as $tab => $tabFields) {
            $this->openTab($tab)->setFieldsData($tabFields, $this->_rootElement);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function openContainer($tabName)
    {
        return $this->openTab($tabName);
    }

    /**
     * Open tab.
     *
     * @param string $tabName
     * @return $this
     * @throws \Exception
     */
    public function openTab($tabName)
    {
        $this->browser->find($this->header)->hover();
        if (!$this->isTabVisible($tabName)) {
            throw new \Exception(
                'Tab "' . $tabName . '" is not visible.'
            );
        }
        $this->browser->find($this->header)->hover();
        $this->clickOnTabName($tabName);
        $this->browser->find($this->header)->hover();

        return $this;
    }

    /**
     * Click on tab name.
     *
     * @param string $tabName
     * @return void
     */
    protected function clickOnTabName($tabName)
    {
        $this->getContainerElement($tabName)->click();
    }

    /**
     * Check whether tab is visible.
     *
     * @param string $tabName
     * @return bool
     */
    public function isTabVisible($tabName)
    {
        return $this->getContainerElement($tabName)->isVisible();
    }
}
