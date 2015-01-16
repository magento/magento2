<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Block\Widget;

use Mtf\Block\Form as AbstractForm;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;

/**
 * Class Tab
 * Is used to represent any tab on the page
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
    protected $mageErrorField = '//*[contains(@class,"field ")][.//*[@class="mage-error"]]';

    /**
     * Fields label with mage error.
     *
     * @var string
     */
    protected $mageErrorLabel = './label';

    /**
     * Mage error text.
     *
     * @var string
     */
    protected $mageErrorText = './/*[@class="mage-error"]';

    /**
     * Fill data to fields on tab
     *
     * @param array $fields
     * @param Element|null $element
     * @return $this
     */
    public function fillFormTab(array $fields, Element $element = null)
    {
        $data = $this->dataMapping($fields);
        $this->_fill($data, $element);

        return $this;
    }

    /**
     * Get data of tab
     *
     * @param array|null $fields
     * @param Element|null $element
     * @return array
     */
    public function getDataFormTab($fields = null, Element $element = null)
    {
        $data = $this->dataMapping($fields);
        return $this->_getData($data, $element);
    }

    /**
     * Update data to fields on tab
     *
     * @param array $fields
     * @param Element|null $element
     */
    public function updateFormTab(array $fields, Element $element = null)
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
        $elements = $this->_rootElement->find($this->mageErrorField, Locator::SELECTOR_XPATH)->getElements();
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
