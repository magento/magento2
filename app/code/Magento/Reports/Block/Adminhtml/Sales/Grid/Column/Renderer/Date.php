<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Block\Adminhtml\Sales\Grid\Column\Renderer;

use Magento\Framework\Locale\Bundle\DataBundle;

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
            if (self::$_format === null) {
                try {
                    $formats = (new DataBundle())->get(
                        $this->_localeResolver->getLocale()
                    )['calendar']['gregorian']['availableFormats'];

                    switch ($this->getColumn()->getPeriodType()) {
                        case 'month':
                            self::$_format = $formats['yM'];
                            break;

                        case 'year':
                            self::$_format = $formats['y'];
                            break;

                        default:
                            self::$_format = $this->_localeDate->getDateFormat(
                                \IntlDateFormatter::MEDIUM
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
        //@todo: check this logic manually
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
                $data = $this->getColumn()->getGmtoffset()
                    ? \IntlDateFormatter::formatObject(
                        $this->_localeDate->date(new \DateTime($data)),
                        $format
                    )
                    : \IntlDateFormatter::formatObject(
                        $this->_localeDate->date(
                            new \DateTime($data),
                            null,
                            false
                        ),
                        $format
                    );
            } catch (\Exception $e) {
                $data = $this->getColumn()->getTimezone()
                    ? \IntlDateFormatter::formatObject(
                        $this->_localeDate->date(new \DateTime($data)),
                        $format
                    )
                    : \IntlDateFormatter::formatObject(
                        $this->_localeDate->date(
                            new \DateTime($data),
                            null,
                            false
                        ),
                        $format
                    );
            }
            return $data;
        }
        return $this->getColumn()->getDefault();
    }
}
