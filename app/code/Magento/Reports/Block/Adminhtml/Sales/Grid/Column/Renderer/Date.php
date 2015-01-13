<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Block\Adminhtml\Sales\Grid\Column\Renderer;

/**
 * Adminhtml grid item renderer date
 */
class Date extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Date
{
    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_localeResolver = $localeResolver;
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
                    $localeCode = $this->_localeResolver->getLocaleCode();
                    $localeData = new \Zend_Locale_Data();
                    switch ($this->getColumn()->getPeriodType()) {
                        case 'month':
                            self::$_format = $localeData->getContent($localeCode, 'dateitem', 'yM');
                            break;

                        case 'year':
                            self::$_format = $localeData->getContent($localeCode, 'dateitem', 'y');
                            break;

                        default:
                            self::$_format = $this->_localeDate->getDateFormat(
                                \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_MEDIUM
                            );
                            break;
                    }
                } catch (\Exception $e) {
                }
            }
            $format = self::$_format;
        }
        return $format;
    }

    /**
     * Renders grid column
     *
     * @param \Magento\Framework\Object $row
     * @return string
     */
    public function render(\Magento\Framework\Object $row)
    {
        if ($data = $row->getData($this->getColumn()->getIndex())) {
            switch ($this->getColumn()->getPeriodType()) {
                case 'month':
                    $dateFormat = 'yyyy-MM';
                    break;
                case 'year':
                    $dateFormat = 'yyyy';
                    break;
                default:
                    $dateFormat = \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT;
                    break;
            }

            $format = $this->_getFormat();
            try {
                $data = $this->getColumn()->getGmtoffset() ? $this->_localeDate->date(
                    $data,
                    $dateFormat
                )->toString(
                    $format
                ) : $this->_localeDate->date(
                    $data,
                    \Zend_Date::ISO_8601,
                    null,
                    false
                )->toString(
                    $format
                );
            } catch (\Exception $e) {
                $data = $this->getColumn()->getTimezone() ? $this->_localeDate->date(
                    $data,
                    $dateFormat
                )->toString(
                    $format
                ) : $this->_localeDate->date(
                    $data,
                    $dateFormat,
                    null,
                    false
                )->toString(
                    $format
                );
            }
            return $data;
        }
        return $this->getColumn()->getDefault();
    }
}
