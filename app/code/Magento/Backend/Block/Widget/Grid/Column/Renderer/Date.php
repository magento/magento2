<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;

/**
 * Backend grid item renderer date
 */
class Date extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var int
     */
    protected $_defaultWidth = 160;

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
                \IntlDateFormatter::NONE,
                null,
                $this->getColumn()->getTimezone() !== false ? null : 'UTC'
            );
        }
        return $this->getColumn()->getDefault();
    }
}
