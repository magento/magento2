<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Block\Adminhtml\System\Config;

use Magento\Framework\App\ObjectManager;

/**
 * Provides label with default Time Zone
 */
class CollectionTimeLabel extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $localeResolver;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     * @param \Magento\Framework\Locale\ResolverInterface|null $localeResolver
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = [],
        \Magento\Framework\Locale\ResolverInterface $localeResolver = null
    ) {
        $this->localeResolver = $localeResolver ?:
            ObjectManager::getInstance()->get(\Magento\Framework\Locale\ResolverInterface::class);
        parent::__construct($context, $data);
    }

    /**
     * Add current time zone to comment, properly translated according to locale
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $timeZoneCode = $this->_localeDate->getConfigTimezone();
        $locale = $this->localeResolver->getLocale();
        $getLongTimeZoneName = \IntlTimeZone::createTimeZone($timeZoneCode)
            ->getDisplayName(false, \IntlTimeZone::DISPLAY_LONG, $locale);
        $element->setData(
            'comment',
            sprintf("%s (%s)", $getLongTimeZoneName, $timeZoneCode)
        );
        return parent::render($element);
    }
}
