<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @param   \Magento\Framework\Object $row
     * @return  string
     */
    public function render(\Magento\Framework\Object $row)
    {
        $format = $this->getColumn()->getFormat();
        if ($data = $this->_getValue($row)) {
            return $this->_localeDate->formatDateTime(
                $data instanceof \DateTimeInterface ? $data : new \DateTime($data),
                $format ?: \IntlDateFormatter::MEDIUM,
                $format ?: \IntlDateFormatter::MEDIUM
            );
        }
        return $this->getColumn()->getDefault();
    }
}
