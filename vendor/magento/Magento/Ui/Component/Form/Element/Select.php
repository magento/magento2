<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Ui\Component\Form\Element;

/**
 * Class Select
 */
class Select extends AbstractFormElement
{
    /**
     * Check if option value
     *
     * @param string $optionValue
     * @return bool
     */
    public function getIsSelected($optionValue)
    {
        return $this->getValue() == $optionValue;
    }
}
