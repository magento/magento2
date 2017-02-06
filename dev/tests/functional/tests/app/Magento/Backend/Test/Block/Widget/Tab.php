<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Block\Widget;

use Magento\Ui\Test\Block\Adminhtml\AbstractContainer;
use Magento\Mtf\Client\Locator;

/**
 * Is used to represent any tab on the page.
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class Tab extends AbstractContainer
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
