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

/**
 * Recurring profile info/options product view block
 */
namespace Magento\RecurringProfile\Block\Catalog\Product\View;

class Profile extends \Magento\View\Element\Template
{
    /**
     * Recurring profile instance
     *
     * @var \Magento\RecurringProfile\Model\RecurringProfile
     */
    protected $_profile = false;

    /**
     * Core registry
     *
     * @var \Magento\Registry
     */
    protected $_registry = null;

    /**
     * Recurring profile factory
     *
     * @var \Magento\RecurringProfile\Model\RecurringProfileFactory
     */
    protected $_profileFactory;

    /**
     * @param \Magento\View\Element\Template\Context $context
     * @param \Magento\Registry $registry
     * @param \Magento\RecurringProfile\Model\RecurringProfileFactory $profileFactory
     * @param array $data
     */
    public function __construct(
        \Magento\View\Element\Template\Context $context,
        \Magento\Registry $registry,
        \Magento\RecurringProfile\Model\RecurringProfileFactory $profileFactory,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->_registry = $registry;
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
                ->createBlock('Magento\View\Element\Html\Date')
                ->setId('recurring_start_date')
                ->setName(\Magento\RecurringProfile\Model\RecurringProfile::BUY_REQUEST_START_DATETIME)
                ->setClass('datetime-picker input-text')
                ->setImage($this->getViewFileUrl('Magento_Core::calendar.gif'))
                ->setDateFormat($this->_locale->getDateFormat(\Magento\Core\Model\LocaleInterface::FORMAT_TYPE_SHORT))
                ->setTimeFormat($this->_locale->getTimeFormat(\Magento\Core\Model\LocaleInterface::FORMAT_TYPE_SHORT));
            return $calendar->getHtml();
        }
        return '';
    }

    /**
     * Determine current product and initialize its recurring profile model
     *
     * @return \Magento\RecurringProfile\Block\Catalog\Product\View\Profile
     */
    protected function _prepareLayout()
    {
        $product = $this->_registry->registry('current_product');
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
