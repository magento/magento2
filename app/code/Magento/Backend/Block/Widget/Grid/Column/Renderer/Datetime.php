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
     * Date format string
     *
     * @var string
     */
    protected static $_format = null;

    /**
     * Retrieve datetime format
     *
     * @return string|null
     */
    protected function _getFormat()
    {
        $format = $this->getColumn()->getFormat();
        if (!$format) {
            if (is_null(self::$_format)) {
                try {
                    self::$_format = $this->_localeDate->getDateTimeFormat(
                        \IntlDateFormatter::MEDIUM
                    );
                } catch (\Exception $e) {
                    $this->_logger->critical($e);
                }
            }
            $format = self::$_format;
        }
        return $format;
    }

    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\Object $row
     * @return  string
     */
    public function render(\Magento\Framework\Object $row)
    {
        if ($data = $this->_getValue($row)) {
            return $this->_localeDate->formatDateTime(new \DateTime($data));
        }
        return $this->getColumn()->getDefault();
    }
}
