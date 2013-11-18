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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Recurring profile info/options product view block
 */
namespace Magento\Payment\Block\Catalog\Product\View;

class Profile extends \Magento\Core\Block\Template
{
    /**
     * Recurring profile instance
     *
     * @var \Magento\Payment\Model\Recurring\Profile
     */
    protected $_profile = false;

    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Locale model
     *
     * @var \Magento\Core\Model\LocaleInterface
     */
    protected $_locale;

    /**
     * Recurring profile factory
     *
     * @var \Magento\Payment\Model\Recurring\ProfileFactory
     */
    protected $_profileFactory;

    /**
     * Construct
     *
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Block\Template\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param \Magento\Payment\Model\Recurring\ProfileFactory $profileFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Block\Template\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\Core\Model\LocaleInterface $locale,
        \Magento\Payment\Model\Recurring\ProfileFactory $profileFactory,
        array $data = array()
    ) {
        parent::__construct($coreData, $context, $data);
        $this->_coreRegistry = $registry;
        $this->_locale = $locale;
        $this->_profileFactory = $profileFactory;
    }

    /**
     * Getter for schedule info
     * array(
     *     <title> => array('blah-blah', 'bla-bla-blah', ...)
     *     <title2> => ...
     * )
     * @return array
     */
    public function getScheduleInfo()
    {
        $scheduleInfo = array();
        foreach ($this->_profile->exportScheduleInfo() as $info) {
            $scheduleInfo[$info->getTitle()] = $info->getSchedule();
        }
        return $scheduleInfo;
    }

    /**
     * Render date input element
     *
     * @return string
     */
    public function getDateHtml()
    {
        if ($this->_profile->getStartDateIsEditable()) {
            $this->setDateHtmlId('recurring_start_date');
            $calendar = $this->getLayout()
                ->createBlock('Magento\Core\Block\Html\Date')
                ->setId('recurring_start_date')
                ->setName(\Magento\Payment\Model\Recurring\Profile::BUY_REQUEST_START_DATETIME)
                ->setClass('datetime-picker input-text')
                ->setImage($this->getViewFileUrl('Magento_Core::calendar.gif'))
                ->setDateFormat($this->_locale->getDateFormat(\Magento\Core\Model\LocaleInterface::FORMAT_TYPE_SHORT))
                ->setTimeFormat($this->_locale->getTimeFormat(\Magento\Core\Model\LocaleInterface::FORMAT_TYPE_SHORT));
            return $calendar->getHtml();
        }
    }

    /**
     * Determine current product and initialize its recurring profile model
     *
     * @return \Magento\Payment\Block\Catalog\Product\View\Profile
     */
    protected function _prepareLayout()
    {
        $product = $this->_coreRegistry->registry('current_product');
        if ($product) {
            $this->_profile = $this->_profileFactory->create()->importProduct($product);
        }
        return parent::_prepareLayout();
    }

    /**
     * If there is no profile information, the template will be unset, blocking the output
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_profile) {
            $this->_template = null;
        }
        return parent::_toHtml();
    }
}
