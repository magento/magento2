<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

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
     * Date format string
     *
     * @var string
     */
    protected static $_format = null;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }

    /**
     * Retrieve date format
     *
     * @return string
     */
    protected function _getFormat()
    {
        $format = $this->getColumn()->getFormat();
        if (!$format) {
            if (is_null(self::$_format)) {
                try {
                    self::$_format = $this->_localeDate->getDateFormat(
                        \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_MEDIUM
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
        if ($data = $row->getData($this->getColumn()->getIndex())) {
            $format = $this->_getFormat();
            try {
                if ($this->getColumn()->getGmtoffset()) {
                    $data = $this->_localeDate->date(
                        $data,
                        \Magento\Framework\Stdlib\DateTime::DATETIME_INTERNAL_FORMAT
                    )->toString(
                        $format
                    );
                } else {
                    $data = $this->_localeDate->date($data, \Zend_Date::ISO_8601, null, false)->toString($format);
                }
            } catch (\Exception $e) {
                if ($this->getColumn()->getTimezone()) {
                    $data = $this->_localeDate->date(
                        $data,
                        \Magento\Framework\Stdlib\DateTime::DATETIME_INTERNAL_FORMAT
                    )->toString(
                        $format
                    );
                } else {
                    $data = $this->_localeDate->date($data, null, null, false)->toString($format);
                }
            }
            return $data;
        }
        return $this->getColumn()->getDefault();
    }
}
