<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Block\Adminhtml\Promo\Quote\Edit;

use Magento\Backend\Test\Block\Widget\FormTabs;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Sales rule edit form.
 */
class PromoQuoteForm extends FormTabs
{
    /**
     * Selector of element to wait for. If set by child will wait for element after action
     *
     * @var string
     */
    protected $waitForSelector = 'div#promo_catalog_edit_tabs';

    /**
     * Wait for should be for visibility or not?
     *
     * @var boolean
     */
    protected $waitForSelectorVisible = false;

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
        $tabs = $this->getFixtureFieldsByContainers($fixture);
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
}
