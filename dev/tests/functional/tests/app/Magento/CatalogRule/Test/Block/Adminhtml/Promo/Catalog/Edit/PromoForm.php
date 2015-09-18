<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Block\Adminhtml\Promo\Catalog\Edit;

use Magento\Backend\Test\Block\Widget\FormTabs;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Element;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Form for creation of a Catalog Price Rule.
 */
class PromoForm extends FormTabs
{
    /**
     * Add button.
     *
     * @var string
     */
    protected $addButton = '.rule-param-new-child a';

    /**
     * Locator for Customer Segment Conditions.
     *
     * @var string
     */
    protected $conditionFormat = '//*[@id="conditions__1__new_child"]//option[contains(.,"%s")]';

    /**
     * Fill form with tabs.
     *
     * @param FixtureInterface $fixture
     * @param SimpleElement $element
     * @param array $replace
     * @return $this|FormTabs
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null, array $replace = null)
    {
        $tabs = $this->getFieldsByTabs($fixture);
        if ($replace) {
            $tabs = $this->prepareData($tabs, $replace);
        }
        $this->fillTabs($tabs, $element);
    }

    /**
     * Replace placeholders in each values of data.
     *
     * @param array $tabs
     * @param array $replace
     * @return array
     */
    protected function prepareData(array $tabs, array $replace)
    {
        foreach ($replace as $tabName => $fields) {
            foreach ($fields as $key => $pairs) {
                if (isset($tabs[$tabName][$key])) {
                    $tabs[$tabName][$key]['value'] = str_replace(
                        array_keys($pairs),
                        array_values($pairs),
                        $tabs[$tabName][$key]['value']
                    );
                }
            }
        }

        return $tabs;
    }

    /**
     * Check if attribute is available in conditions.
     *
     * @param string $name
     * @return bool
     */
    public function isAttributeInConditions($name)
    {
        $this->_rootElement->find($this->addButton)->click();
        return $this->_rootElement->find(
            sprintf($this->conditionFormat, $name),
            Locator::SELECTOR_XPATH
        )->isVisible();
    }
}
