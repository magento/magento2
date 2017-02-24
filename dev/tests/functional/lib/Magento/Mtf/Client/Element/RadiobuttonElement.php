<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Client\Element;

use Magento\Mtf\Client\Locator;

/**
 * Class provides ability to work with page element radio button.
 */
class RadiobuttonElement extends SimpleElement
{
    /**
     * Label for radio button selector.
     *
     * @var string
     */
    protected $labelSelector = './..//label[contains(., "%s")]';

    /**
     * Selector for selected label.
     *
     * @var string
     */
    protected $selectedLabelSelector = 'input[type=radio]:checked + label';

    /**
     * Get value of the required element.
     *
     * @return string
     */
    public function getValue()
    {
        $this->eventManager->dispatchEvent(['get_value'], [$this->getAbsoluteSelector()]);

        return $this->find($this->selectedLabelSelector)->getText();
    }

    /**
     * Select radio button based on label value.
     *
     * @param string $value
     * @return void
     */
    public function setValue($value)
    {
        $this->eventManager->dispatchEvent(['set_value'], [__METHOD__, $this->getAbsoluteSelector()]);

        $radioButtonLabel = $this->find(sprintf($this->labelSelector, $value), Locator::SELECTOR_XPATH);
        if (!$this->isSelected()) {
            $radioButtonLabel->click();
        }
    }
}
