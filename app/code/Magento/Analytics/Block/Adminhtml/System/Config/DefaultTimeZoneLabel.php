<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use \Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class DefaultTimeZoneLabelLabel.
 *
 * Provides label with default Time Zone
 */
class DefaultTimeZoneLabel extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var TimezoneInterface
     */
    private $timeZone;

    /**
     * SubscriptionStatusLabel constructor.
     *
     * @param Context $context
     * @param TimezoneInterface $timeZone
     * @param array $data
     */
    public function __construct(
        Context $context,
        TimezoneInterface $timeZone,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->timeZone = $timeZone;
    }

    /**
     * Get default time zone
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $timeZoneCode = $this->timeZone->getConfigTimezone();
        $getLongTimeZoneName = \IntlTimeZone::createTimeZone($timeZoneCode)->getDisplayName();
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        $element->setData(
            'value',
            sprintf("%s (%s)", $getLongTimeZoneName, $timeZoneCode)
        );
        return parent::render($element);
    }
}
