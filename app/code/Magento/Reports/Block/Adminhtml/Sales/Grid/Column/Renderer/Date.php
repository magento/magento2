<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Block\Adminhtml\Sales\Grid\Column\Renderer;

use Magento\Framework\Locale\Bundle\DataBundle;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;

/**
 * Adminhtml grid item renderer date
 */
class Date extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Date
{

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $localeResolver;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Context $context
     * @param DateTimeFormatterInterface $dateTimeFormatter
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        DateTimeFormatterInterface $dateTimeFormatter,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        array $data = []
    ) {
        parent::__construct($context, $dateTimeFormatter, $data);
        $this->localeResolver = $localeResolver;
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
            $dataBundle = new DataBundle();
            $resourceBundle = $dataBundle->get($this->localeResolver->getLocale());
            $formats = $resourceBundle['calendar']['gregorian']['availableFormats'];
            switch ($this->getColumn()->getPeriodType()) {
                case 'month':
                    $format = $formats['yM'];
                    break;
                case 'year':
                    $format = $formats['y'];
                    break;
                default:
                    $format = $this->_localeDate->getDateFormat(\IntlDateFormatter::MEDIUM);
                    break;
            }
        }
        return $format;
    }

    /**
     * Renders grid column
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if ($data = $row->getData($this->getColumn()->getIndex())) {
            switch ($this->getColumn()->getPeriodType()) {
                case 'month':
                    $data = $data . '-01';
                    break;
                case 'year':
                    $data = $data . '-01-01';
                    break;
            }
            $format = $this->_getFormat();
            if ($this->getColumn()->getGmtoffset() || $this->getColumn()->getTimezone()) {
                $date = $this->_localeDate->date(new \DateTime($data));
            } else {
                $date = $this->_localeDate->date(new \DateTime($data), null, false);
            }
            return $this->dateTimeFormatter->formatObject($date, $format, $this->localeResolver->getLocale());
        }
        return $this->getColumn()->getDefault();
    }
}
