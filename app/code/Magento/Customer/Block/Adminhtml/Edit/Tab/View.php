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
 * @category    Magento
 * @package     Magento_Customer
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Block\Adminhtml\Edit\Tab;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Customer\Service\V1\CustomerAccountServiceInterface;
use Magento\Customer\Service\V1\Data\AddressConverter;
use Magento\Exception\NoSuchEntityException;

/**
 * Customer account form block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class View extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Customer\Service\V1\Data\Customer
     */
    protected $_customer;

    /**
     * @var \Magento\Log\Model\Customer
     */
    protected $_customerLog;

    /**
     * Core registry
     *
     * @var \Magento\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Log\Model\Visitor
     */
    protected $_modelVisitor;

    /**
     * @var CustomerAccountServiceInterface
     */
    protected $_accountService;

    /**
     * @var \Magento\Customer\Service\V1\CustomerAddressServiceInterface
     */
    protected $_addressService;

    /**
     * @var \Magento\Customer\Service\V1\CustomerGroupServiceInterface
     */
    protected $_groupService;

    /**
     * @var \Magento\Customer\Service\V1\Data\CustomerBuilder
     */
    protected $_customerBuilder;

    /**
     * @var \Magento\Customer\Helper\Address
     */
    protected $_addressHelper;

    /**
     * @var \Magento\Log\Model\CustomerFactory
     */
    protected $_logFactory;

    /**
     * @var \Magento\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param CustomerAccountServiceInterface $accountService
     * @param \Magento\Customer\Service\V1\CustomerAddressServiceInterface $addressService
     * @param \Magento\Customer\Service\V1\CustomerGroupServiceInterface $groupService
     * @param \Magento\Customer\Service\V1\Data\CustomerBuilder $customerBuilder
     * @param \Magento\Customer\Helper\Address $addressHelper
     * @param \Magento\Log\Model\CustomerFactory $logFactory
     * @param \Magento\Registry $registry
     * @param \Magento\Log\Model\Visitor $modelVisitor
     * @param \Magento\Stdlib\DateTime $dateTime
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        CustomerAccountServiceInterface $accountService,
        \Magento\Customer\Service\V1\CustomerAddressServiceInterface $addressService,
        \Magento\Customer\Service\V1\CustomerGroupServiceInterface $groupService,
        \Magento\Customer\Service\V1\Data\CustomerBuilder $customerBuilder,
        \Magento\Customer\Helper\Address $addressHelper,
        \Magento\Log\Model\CustomerFactory $logFactory,
        \Magento\Registry $registry,
        \Magento\Log\Model\Visitor $modelVisitor,
        \Magento\Stdlib\DateTime $dateTime,
        array $data = array()
    ) {
        $this->_coreRegistry = $registry;
        $this->_modelVisitor = $modelVisitor;
        $this->_accountService = $accountService;
        $this->_addressService = $addressService;
        $this->_groupService = $groupService;
        $this->_customerBuilder = $customerBuilder;
        $this->_addressHelper = $addressHelper;
        $this->_logFactory = $logFactory;
        $this->dateTime = $dateTime;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Customer\Service\V1\Data\Customer
     */
    public function getCustomer()
    {
        if (!$this->_customer) {
            $this->_customer = $this->_customerBuilder->populateWithArray(
                $this->_backendSession->getCustomerData()['account']
            )->create();
        }
        return $this->_customer;
    }

    /**
     * @return string|null
     */
    public function getCustomerId()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * @param int $groupId
     * @return \Magento\Customer\Service\V1\Data\CustomerGroup|null
     */
    private function getGroup($groupId)
    {
        try {
            $group = $this->_groupService->getGroup($groupId);
        } catch (\Magento\Exception\NoSuchEntityException $e) {
            $group = null;
        }
        return $group;
    }

    /**
     * @return string|null
     */
    public function getGroupName()
    {
        $customer = $this->getCustomer();

        if ($groupId = $customer->getId() ? $customer->getGroupId() : null) {
            if ($group = $this->getGroup($groupId)) {
                return $group->getCode();
            }
        }

        return null;
    }

    /**
     * Load Customer Log model
     *
     * @return \Magento\Log\Model\Customer
     */
    public function getCustomerLog()
    {
        if (!$this->_customerLog) {
            $this->_customerLog = $this->_logFactory->create()->loadByCustomer($this->getCustomerId());
        }
        return $this->_customerLog;
    }

    /**
     * Get customer creation date
     *
     * @return string
     */
    public function getCreateDate()
    {
        return $this->formatDate(
            $this->getCustomer()->getCreatedAt(),
            \Magento\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_MEDIUM,
            true
        );
    }

    /**
     * @return string
     */
    public function getStoreCreateDate()
    {
        $date = $this->_localeDate->scopeDate(
            $this->getCustomer()->getStoreId(),
            $this->getCustomer()->getCreatedAt(),
            true
        );
        return $this->formatDate($date, \Magento\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_MEDIUM, true);
    }

    /**
     * @return string
     */
    public function getStoreCreateDateTimezone()
    {
        return $this->_storeConfig->getConfig(
            $this->_localeDate->getDefaultTimezonePath(),
            $this->getCustomer()->getStoreId()
        );
    }

    /**
     * Get customer last login date
     *
     * @return string
     */
    public function getLastLoginDate()
    {
        $date = $this->getCustomerLog()->getLoginAtTimestamp();
        if ($date) {
            return $this->formatDate($date, \Magento\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_MEDIUM, true);
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
            return $this->formatDate($date, \Magento\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_MEDIUM, true);
        }
        return __('Never');
    }

    /**
     * @return string
     */
    public function getStoreLastLoginDateTimezone()
    {
        return $this->_storeConfig->getConfig(
            $this->_localeDate->getDefaultTimezonePath(),
            $this->getCustomer()->getStoreId()
        );
    }

    /**
     * @return string
     */
    public function getCurrentStatus()
    {
        $log = $this->getCustomerLog();
        $interval = $this->_modelVisitor->getOnlineMinutesInterval();
        if ($log->getLogoutAt() || strtotime(
            $this->dateTime->now()
        ) - strtotime(
            $log->getLastVisitAt()
        ) > $interval * 60
        ) {
            return __('Offline');
        }
        return __('Online');
    }

    /**
     * @return string
     */
    public function getIsConfirmedStatus()
    {
        $id = $this->getCustomerId();
        switch ($this->_accountService->getConfirmationStatus($id)) {
            case CustomerAccountServiceInterface::ACCOUNT_CONFIRMED:
                return __('Confirmed');
            case CustomerAccountServiceInterface::ACCOUNT_CONFIRMATION_REQUIRED:
                return __('Confirmation Required');
            case CustomerAccountServiceInterface::ACCOUNT_CONFIRMATION_NOT_REQUIRED:
                return __('Confirmation Not Required');
        }
        return __('Indeterminate');
    }

    /**
     * @return null|string
     */
    public function getCreatedInStore()
    {
        return $this->_storeManager->getStore($this->getStoreId())->getName();
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->getCustomer()->getStoreId();
    }

    /**
     * @return string|null
     */
    public function getBillingAddressHtml()
    {
        try {
            $address = $this->_addressService->getAddress($this->getCustomer()->getDefaultBilling());
        } catch (NoSuchEntityException $e) {
            return __('The customer does not have default billing address.');
        }
        return $this->_addressHelper->getFormatTypeRenderer(
            'html'
        )->renderArray(
            AddressConverter::toFlatArray($address)
        );
    }

    /**
     * @return string
     */
    public function getAccordionHtml()
    {
        return $this->getChildHtml('accordion');
    }

    /**
     * @return string
     */
    public function getSalesHtml()
    {
        return $this->getChildHtml('sales');
    }

    /**
     * @return string
     */
    public function getTabLabel()
    {
        return __('Customer View');
    }

    /**
     * @return string
     */
    public function getTabTitle()
    {
        return __('Customer View');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        if ($this->getCustomerId()) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        if ($this->getCustomerId()) {
            return false;
        }
        return true;
    }
}
