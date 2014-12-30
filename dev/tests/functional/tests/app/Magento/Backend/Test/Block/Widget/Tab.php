<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Backend\Test\Block\Widget;

use Mtf\Block\Form as AbstractForm;
use Mtf\Client\Element\SimpleElement;
use Mtf\Client\Locator;

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
     * Get data of tab
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
     * Update data to fields on tab
     *
     * @param array $fields
     * @param SimpleElement|null $element
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
