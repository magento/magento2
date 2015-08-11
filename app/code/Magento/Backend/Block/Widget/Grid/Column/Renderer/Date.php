<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

use Magento\Framework\Stdlib\DateTime\DateTimeFormatter;

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
     * @var DateTimeFormatter
     */
    protected $dateTimeFormatter;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param DateTimeFormatter $dateTimeFormatter
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        DateTimeFormatter $dateTimeFormatter,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dateTimeFormatter = $dateTimeFormatter;
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
            if (self::$_format === null) {
                try {
                    self::$_format = $this->_localeDate->getDateFormat(
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
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if ($data = $row->getData($this->getColumn()->getIndex())) {
            return $this->dateTimeFormatter->formatObject(
                $this->_localeDate->date(new \DateTime($data)),
                $this->_getFormat()
            );
        }
        return $this->getColumn()->getDefault();
    }
}
