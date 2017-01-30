<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Backend grid item renderer datetime
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

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
        if ($date = $this->_getValue($row)) {
            return $this->_localeDate->formatDateTime(
                $date instanceof \DateTimeInterface ? $date : new \DateTime($date),
                $format ?: \IntlDateFormatter::MEDIUM,
                $format ?: \IntlDateFormatter::MEDIUM,
                null,
                $this->getColumn()->getTimezone() !== false ? null : 'UTC'
            );
        }
        return $this->getColumn()->getDefault();
    }
}
