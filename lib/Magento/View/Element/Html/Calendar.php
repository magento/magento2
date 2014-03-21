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
namespace Magento\View\Element\Html;

/**
 * Calendar block for page header
 *
 * Prepares localization data for calendar
 */
class Calendar extends \Magento\View\Element\Template
{
    /**
     * Date model
     *
     * @var \Magento\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * JSON Encoder
     *
     * @var \Magento\Json\EncoderInterface
     */
    protected $encoder;

    /**
     * @var \Magento\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * Constructor
     *
     * @param \Magento\View\Element\Template\Context $context
     * @param \Magento\Stdlib\DateTime\DateTime $date
     * @param \Magento\Json\EncoderInterface $encoder
     * @param \Magento\Locale\ResolverInterface $localeResolver
     * @param array $data
     */
    public function __construct(
        \Magento\View\Element\Template\Context $context,
        \Magento\Stdlib\DateTime\DateTime $date,
        \Magento\Json\EncoderInterface $encoder,
        \Magento\Locale\ResolverInterface $localeResolver,
        array $data = array()
    ) {
        $this->_date = $date;
        $this->encoder = $encoder;
        $this->_localeResolver = $localeResolver;
        parent::__construct($context, $data);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $localeCode = $this->_localeResolver->getLocaleCode();

        // get days names
        $days = \Zend_Locale_Data::getList($localeCode, 'days');
        $this->assign(
            'days',
            array(
                'wide' => $this->encoder->encode(array_values($days['format']['wide'])),
                'abbreviated' => $this->encoder->encode(array_values($days['format']['abbreviated']))
            )
        );

        // get months names
        $months = \Zend_Locale_Data::getList($localeCode, 'months');
        $this->assign(
            'months',
            array(
                'wide' => $this->encoder->encode(array_values($months['format']['wide'])),
                'abbreviated' => $this->encoder->encode(array_values($months['format']['abbreviated']))
            )
        );

        // get "today" and "week" words
        $this->assign('today', $this->encoder->encode(\Zend_Locale_Data::getContent($localeCode, 'relative', 0)));
        $this->assign('week', $this->encoder->encode(\Zend_Locale_Data::getContent($localeCode, 'field', 'week')));

        // get "am" & "pm" words
        $this->assign('am', $this->encoder->encode(\Zend_Locale_Data::getContent($localeCode, 'am')));
        $this->assign('pm', $this->encoder->encode(\Zend_Locale_Data::getContent($localeCode, 'pm')));

        // get first day of week and weekend days
        $this->assign('firstDay', (int)$this->_storeConfig->getConfig('general/locale/firstday'));
        $this->assign(
            'weekendDays',
            $this->encoder->encode((string)$this->_storeConfig->getConfig('general/locale/weekend'))
        );

        // define default format and tooltip format
        $this->assign(
            'defaultFormat',
            $this->encoder->encode(
                $this->_localeDate->getDateFormat(\Magento\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_MEDIUM)
            )
        );
        $this->assign(
            'toolTipFormat',
            $this->encoder->encode(
                $this->_localeDate->getDateFormat(\Magento\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_LONG)
            )
        );

        // get days and months for en_US locale - calendar will parse exactly in this locale
        $days = \Zend_Locale_Data::getList('en_US', 'days');
        $months = \Zend_Locale_Data::getList('en_US', 'months');
        $enUS = new \stdClass();
        $enUS->m = new \stdClass();
        $enUS->m->wide = array_values($months['format']['wide']);
        $enUS->m->abbr = array_values($months['format']['abbreviated']);
        $this->assign('enUS', $this->encoder->encode($enUS));

        return parent::_toHtml();
    }

    /**
     * Return offset of current timezone with GMT in seconds
     *
     * @return int
     */
    public function getTimezoneOffsetSeconds()
    {
        return $this->_date->getGmtOffset();
    }

    /**
     * Getter for store timestamp based on store timezone settings
     *
     * @param null|string|bool|int|\Magento\Core\Model\Store $store
     * @return int
     */
    public function getStoreTimestamp($store = null)
    {
        return $this->_localeDate->scopeTimeStamp($store);
    }
}
