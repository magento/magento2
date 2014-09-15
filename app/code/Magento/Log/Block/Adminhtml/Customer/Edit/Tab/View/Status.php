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
namespace Magento\Log\Block\Adminhtml\Customer\Edit\Tab\View;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class Status
 * @package Magento\Log\Block\Adminhtml\Customer\Edit\Tab\View
 */
class Status extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Customer\Service\V1\Data\Customer
     */
    protected $customer;

    /**
     * @var \Magento\Log\Model\Customer
     */
    protected $customerLog;

    /**
     * @var \Magento\Log\Model\Visitor
     */
    protected $modelLog;

    /**
     * @var \Magento\Log\Model\CustomerFactory
     */
    protected $logFactory;

    /**
     * @var \Magento\Customer\Service\V1\Data\CustomerBuilder
     */
    protected $customerBuilder;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Log\Model\CustomerFactory $logFactory
     * @param \Magento\Log\Model\Log $modelLog
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Customer\Service\V1\Data\CustomerBuilder $customerBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Log\Model\CustomerFactory $logFactory,
        \Magento\Log\Model\Log $modelLog,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Customer\Service\V1\Data\CustomerBuilder $customerBuilder,
        array $data = array()
    ) {
        $this->logFactory = $logFactory;
        $this->modelLog = $modelLog;
        $this->dateTime = $dateTime;
        $this->customerBuilder = $customerBuilder;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getStoreLastLoginDateTimezone()
    {
        return $this->_scopeConfig->getValue(
            $this->_localeDate->getDefaultTimezonePath(),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getCustomer()->getStoreId()
        );
    }

    /**
     * @return \Magento\Customer\Service\V1\Data\Customer
     */
    public function getCustomer()
    {
        if (!$this->customer) {
            $this->customer = $this->customerBuilder->populateWithArray(
                $this->_backendSession->getCustomerData()['account']
            )->create();
        }
        return $this->customer;
    }

    /**
     * Get customer's current status
     *
     * @return string
     */
    public function getCurrentStatus()
    {
        $log = $this->getCustomerLog();
        $interval = $this->modelLog->getOnlineMinutesInterval();
        if ($log->getLogoutAt() ||
            strtotime($this->dateTime->now()) - strtotime($log->getLastVisitAt()) > $interval * 60
        ) {
            return __('Offline');
        }
        return __('Online');
    }

    /**
     * Get customer last login date
     *
     * @return string
     */
    public function getLastLoginDate()
    {
        $date = $this->getCustomerLog()->getLoginAt();
        if ($date) {
            return $this->formatDate($date, TimezoneInterface::FORMAT_TYPE_MEDIUM, true);
        }
        return __('Never');
    }

    /**
     * @return string
     */
    public function getStoreLastLoginDate()
    {
        $date = $this->getCustomerLog()->getLoginAtTimestamp();
        if ($date) {
            $date = $this->_localeDate->scopeDate($this->getCustomer()->getStoreId(), $date, true);
            return $this->formatDate($date, TimezoneInterface::FORMAT_TYPE_MEDIUM, true);
        }
        return __('Never');
    }

    /**
     * Load Customer Log model
     *
     * @return \Magento\Log\Model\Customer
     */
    public function getCustomerLog()
    {
        if (!$this->customerLog) {
            $this->customerLog = $this->logFactory->create()->loadByCustomer($this->getCustomer()->getId());
        }
        return $this->customerLog;
    }
}
