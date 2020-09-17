<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Locale\ResolverInterface;

/**
 * Provides label with default Time Zone
 */
class CollectionTimeLabel extends Field
{
    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @param Context $context
     * @param ResolverInterface $localeResolver
     * @param array $data
     */
    public function __construct(
        Context $context,
        ResolverInterface $localeResolver,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->localeResolver = $localeResolver;
    }

    /**
     * Add current time zone to comment, properly translated according to locale
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        $timeZoneCode = $this->_localeDate->getConfigTimezone();
        $locale = $this->localeResolver->getLocale();
        $getLongTimeZoneName = \IntlTimeZone::createTimeZone($timeZoneCode)
            ->getDisplayName(false, \IntlTimeZone::DISPLAY_LONG, $locale);
        $element->setData(
            'comment',
            sprintf('%s (%s)', $getLongTimeZoneName, $timeZoneCode)
        );
        return parent::render($element);
    }
}
