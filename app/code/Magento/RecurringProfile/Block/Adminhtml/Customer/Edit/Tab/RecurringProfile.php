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
 * Adminhtml customer recurring profiles tab
 */
namespace Magento\RecurringProfile\Block\Adminhtml\Customer\Edit\Tab;

use Magento\Customer\Controller\Adminhtml\Index as CustomerController;
class RecurringProfile
    extends \Magento\RecurringProfile\Block\Adminhtml\Profile\Grid
    implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var int
     */
    protected $_currentCustomerId;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\RecurringProfile\Model\Resource\Profile\CollectionFactory $profileCollection
     * @param \Magento\RecurringProfile\Model\States $recurringStates
     * @param \Magento\RecurringProfile\Block\Fields $fields
     * @param \Magento\RecurringProfile\Model\Method\PaymentMethodsList $payments
     * @param \Magento\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\RecurringProfile\Model\Resource\Profile\CollectionFactory $profileCollection,
        \Magento\RecurringProfile\Model\States $recurringStates,
        \Magento\RecurringProfile\Block\Fields $fields,
        \Magento\RecurringProfile\Model\Method\PaymentMethodsList $payments,
        \Magento\Registry $coreRegistry,
        array $data = array()
    ) {
        $this->_coreRegistry = $coreRegistry;

        // @todo remove usage of REGISTRY_CURRENT_CUSTOMER in advantage of REGISTRY_CURRENT_CUSTOMER_ID
        $currentCustomer = $this->_coreRegistry->registry(CustomerController::REGISTRY_CURRENT_CUSTOMER);
        if ($currentCustomer) {
            $this->_currentCustomerId = $currentCustomer->getId();
        } else {
            $this->_currentCustomerId = $this->_coreRegistry->registry(
                CustomerController::REGISTRY_CURRENT_CUSTOMER_ID
            );
        }

        parent::__construct(
            $context,
            $backendHelper,
            $profileCollection,
            $recurringStates,
            $fields,
            $payments,
            $data
        );
    }

    /**
     * Disable filters and paging
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('customer_edit_tab_recurring_profile');
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Recurring Billing Profiles (beta)');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Recurring Billing Profiles (beta)');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return (bool)$this->_currentCustomerId;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Prepare collection for grid
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        if (!$this->_currentCustomerId) {
            return $this;
        }

        $collection = $this->_profileCollection->create()->addFieldToFilter('customer_id', $this->_currentCustomerId);

        if (!$this->getParam($this->getVarNameSort())) {
            $collection->setOrder('profile_id', 'desc');
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Defines after which tab, this tab should be rendered
     *
     * @return string
     */
    public function getAfter()
    {
        return 'orders';
    }

    /**
     * Return grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('sales/recurringProfile/customerGrid', array('_current' => true));
    }
}
