<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Ui\Filter\Type;

use\Magento\Ui\Filter\View;
use Magento\Ui\Filter\FilterPool;
use Magento\Framework\LocaleInterface;
use Magento\Ui\ContentType\ContentTypeFactory;
use Magento\Framework\View\Element\UiComponent\ConfigBuilderInterface;
use Magento\Framework\View\Element\UiComponent\ConfigFactory;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\View\Element\Template\Context as TemplateContext;

/**
 * Class Date
 */
class Date extends View
{
    /**
     * Timezone library
     *
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * Scope config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Locale resolver
     *
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * Constructor
     *
     * @param TemplateContext $context
     * @param Context $renderContext
     * @param ContentTypeFactory $contentTypeFactory
     * @param ConfigFactory $configFactory
     * @param ConfigBuilderInterface $configBuilder
     * @param FilterPool $filterPool
     * @param ResolverInterface $localeResolver
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        Context $renderContext,
        ContentTypeFactory $contentTypeFactory,
        ConfigFactory $configFactory,
        ConfigBuilderInterface $configBuilder,
        FilterPool $filterPool,
        ResolverInterface $localeResolver,
        array $data = []
    ) {
        $this->localeDate = $context->getLocaleDate();
        $this->scopeConfig = $context->getScopeConfig();
        $this->localeResolver = $localeResolver;
        parent::__construct(
            $context,
            $renderContext,
            $contentTypeFactory,
            $configFactory,
            $configBuilder,
            $filterPool,
            $data
        );
    }

    /**
     * Get condition by data type
     *
     * @param string|array $value
     * @return array|null
     */
    public function getCondition($value)
    {
        return $this->convertValue($value);
    }

    /**
     * Convert value
     *
     * @param array|string $value
     * @return array|null
     */
    protected function convertValue($value)
    {
        if (!empty($value['from']) || !empty($value['to'])) {
            $locale = $this->localeResolver->getLocale();
            if (!empty($value['from'])) {
                $value['orig_from'] = $value['from'];
                $value['from'] = $this->convertDate(strtotime($value['from']), $locale);
            }
            if (!empty($value['to'])) {
                $value['orig_to'] = $value['to'];
                $value['to'] = $this->convertDate(strtotime($value['to']), $locale);
            }
            $value['datetime'] = true;
            $value['locale'] = $locale->toString();
        } else {
            $value = null;
        }

        return $value;
    }

    /**
     * Convert given date to default (UTC) timezone
     *
     * @param int $date
     * @param LocaleInterface $locale
     * @return \Magento\Framework\Stdlib\DateTime\Date|null
     */
    protected function convertDate($date, LocaleInterface $locale)
    {
        try {
            $dateObj = $this->localeDate->date(null, null, $locale, false);

            //set default timezone for store (admin)
            $dateObj->setTimezone(
                $this->scopeConfig->getValue(
                    $this->localeDate->getDefaultTimezonePath(),
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            );

            //set beginning of day
            $dateObj->setHour(00);
            $dateObj->setMinute(00);
            $dateObj->setSecond(00);

            //set date with applying timezone of store
            $dateObj->set($date, null, $locale);

            //convert store date to default date in UTC timezone without DST
            $dateObj->setTimezone(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::DEFAULT_TIMEZONE);

            return $dateObj;
        } catch (\Exception $e) {
            return null;
        }
    }
}
