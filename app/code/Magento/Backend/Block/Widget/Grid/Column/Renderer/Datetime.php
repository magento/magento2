<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

/**
 * Backend grid item renderer datetime
 *
 * @api
 * @deprecated in favour of UI component implementation
 */
class Datetime extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $format = $this->getColumn()->getFormat();
        $date = $this->_getValue($row);
        if ($date) {
            if (!($date instanceof \DateTimeInterface)) {
                $date = new \DateTime($date);
            }
            return $this->_localeDate->formatDateTime(
                $date,
                $format ?: \IntlDateFormatter::MEDIUM,
                $format ?: \IntlDateFormatter::MEDIUM,
                null,
                $this->getColumn()->getTimezone() === false ? 'UTC' : null
            );
        }
        return $this->getColumn()->getDefault();
    }
}
