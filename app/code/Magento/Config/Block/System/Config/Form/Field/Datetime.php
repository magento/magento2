<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Block\System\Config\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Backend system config datetime field renderer
 */
class Datetime extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $format = $this->_localeDate->getDateTimeFormat(
            \IntlDateFormatter::MEDIUM
        );
        return \IntlDateFormatter::formatObject($this->_localeDate->date(intval($element->getValue())), $format);
    }
}
