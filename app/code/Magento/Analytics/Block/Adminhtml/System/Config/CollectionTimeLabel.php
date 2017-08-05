<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Block\Adminhtml\System\Config;

/**
 * Provides label with default Time Zone
 */
class CollectionTimeLabel extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Add default time zone to comment
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $timeZoneCode = $this->_localeDate->getConfigTimezone();
        $getLongTimeZoneName = \IntlTimeZone::createTimeZone($timeZoneCode)->getDisplayName();
        $element->setData(
            'comment',
            sprintf("%s (%s)", $getLongTimeZoneName, $timeZoneCode)
        );
        return parent::render($element);
    }
}
