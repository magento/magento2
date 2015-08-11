<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Block\System\Config\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatter;

/**
 * Backend system config datetime field renderer
 */
class Datetime extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var DateTimeFormatter
     */
    protected $dateTimeFormatter;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param DateTimeFormatter $dateTimeFormatter
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        DateTimeFormatter $dateTimeFormatter,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dateTimeFormatter = $dateTimeFormatter;
    }

    /**
     * @param AbstractElement $element
     * @return string
     * @codeCoverageIgnore
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->dateTimeFormatter->formatObject(
            $this->_localeDate->date(intval($element->getValue())),
            $this->_localeDate->getDateTimeFormat(\IntlDateFormatter::MEDIUM)
        );
    }
}
