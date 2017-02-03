<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Block\Widget;

use Magento\Mtf\Block\Form as AbstractForm;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;

/**
 * Is used to represent any tab on the page.
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class Tab extends AbstractForm
{
    /**
     * Field with Mage error.
     *
     * @var string
     */
    protected $mageErrorField = '//fieldset/*[contains(@class,"field ")][.//*[contains(@class,"error")]]';

    /**
     * Fields label with mage error.
     *
     * @var string
     */
    protected $mageErrorLabel = './/*[contains(@class,"label")]';

    /**
     * Mage error text.
     *
     * @var string
     */
    protected $mageErrorText = './/label[contains(@class,"error")]';

    /**
     * Fill data to fields on tab.
     *
     * @param array $fields
     * @param SimpleElement|null $element
     * @return $this
     */
    public function fillFormTab(array $fields, SimpleElement $element = null)
    {
        $data = $this->dataMapping($fields);
        $this->_fill($data, $element);

        return $this;
    }

    /**
     * Get data of tab.
     *
     * @param array|null $fields
     * @param SimpleElement|null $element
     * @return array
     */
    public function getDataFormTab($fields = null, SimpleElement $element = null)
    {
        $data = $this->dataMapping($fields);
        return $this->_getData($data, $element);
    }

    /**
     * Update data to fields on tab.
     *
     * @param array $fields
     * @param SimpleElement|null $element
     * @return void
     */
    public function updateFormTab(array $fields, SimpleElement $element = null)
    {
        $this->fillFormTab($fields, $element);
    }

    /**
     * Get array of label => js error text.
     *
     * @return array
     */
    public function getJsErrors()
    {
        $data = [];
        $elements = $this->_rootElement->getElements($this->mageErrorField, Locator::SELECTOR_XPATH);
        foreach ($elements as $element) {
            $error = $element->find($this->mageErrorText, Locator::SELECTOR_XPATH);
            if ($error->isVisible()) {
                $label = $element->find($this->mageErrorLabel, Locator::SELECTOR_XPATH)->getText();
                $data[$label] = $error->getText();
            }
        }
        return $data;
    }
}
