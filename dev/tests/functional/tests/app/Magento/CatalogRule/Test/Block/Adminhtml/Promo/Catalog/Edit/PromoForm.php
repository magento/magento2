<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Block\Adminhtml\Promo\Catalog\Edit;

use \Magento\Ui\Test\Block\Adminhtml\FormSections;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Form for creation of a Catalog Price Rule.
 */
class PromoForm extends FormSections
{
    /**
     * Magento form loader.
     *
     * @var string
     */
    protected $spinner = '[data-role="spinner"]';
    
    /**
     * Fill form with tabs.
     *
     * @param FixtureInterface $fixture
     * @param SimpleElement $element
     * @param array $replace
     * @return $this
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null, array $replace = null)
    {
        $this->waitPageToLoad();
        $sections = $this->getFixtureFieldsByContainers($fixture);
        if ($replace) {
            $sections = $this->prepareData($sections, $replace);
        }
        return $this->fillContainers($sections, $element);
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
     * Wait page to load.
     *
     * @return void
     */
    protected function waitPageToLoad()
    {
        $this->waitForElementVisible($this->header);
        $this->waitForElementNotVisible($this->spinner);
    }
}
