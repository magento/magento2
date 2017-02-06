<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Test\Block\Adminhtml;

use Magento\Mtf\Client\Locator;

/**
 * Is used to represent any (collapsible) section on the page.
 */
class Section extends AbstractContainer
{
    /**
     * Field with error.
     *
     * @var string
     */
    protected $errorField = '//fieldset/*[contains(@class,"field ")][.//*[contains(@class,"error")]]';

    /**
     * Error label.
     *
     * @var string
     */
    protected $errorLabel = './/*[contains(@class,"label")]';

    /**
     * Error text.
     *
     * @var string
     */
    protected $errorText = './/label[contains(@class,"error")]';

    /**
     * Locator for section.
     *
     * @var string
     */
    protected $section = '[data-index="%s"]';

    /**
     * Get array of label => validation error text.
     *
     * @return array
     */
    public function getValidationErrors()
    {
        $data = [];
        $elements = $this->_rootElement->getElements($this->errorField, Locator::SELECTOR_XPATH);
        foreach ($elements as $element) {
            $error = $element->find($this->errorText, Locator::SELECTOR_XPATH);
            if ($error->isVisible()) {
                $label = $element->find($this->errorLabel, Locator::SELECTOR_XPATH)->getText();
                $data[$label] = $error->getText();
            }
        }
        return $data;
    }

    /**
     * Check whether section is visible.
     *
     * @param string $sectionName
     * @return bool
     */
    public function isSectionVisible($sectionName)
    {
        return $this->_rootElement->find(sprintf($this->section, $sectionName))->isVisible();
    }
}
