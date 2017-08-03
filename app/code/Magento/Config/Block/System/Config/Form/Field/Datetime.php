<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Block\System\Config\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;

/**
 * Backend system config datetime field renderer
 * @since 2.0.0
 */
class Datetime extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var DateTimeFormatterInterface
     * @since 2.0.0
     */
    protected $dateTimeFormatter;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param DateTimeFormatterInterface $dateTimeFormatter
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        DateTimeFormatterInterface $dateTimeFormatter,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dateTimeFormatter = $dateTimeFormatter;
    }

    /**
     * @param AbstractElement $element
     * @return string
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->dateTimeFormatter->formatObject(
            $this->_localeDate->date(intval($element->getValue())),
            $this->_localeDate->getDateTimeFormat(\IntlDateFormatter::MEDIUM)
        );
    }
}
