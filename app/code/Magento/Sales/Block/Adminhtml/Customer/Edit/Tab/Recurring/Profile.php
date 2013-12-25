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
 * @package     Magento_Sales
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml customer recurring profiles tab
 *
 * @author Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Sales\Block\Adminhtml\Customer\Edit\Tab\Recurring;

class Profile
    extends \Magento\Sales\Block\Adminhtml\Recurring\Profile\Grid
    implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Core\Model\Url $urlModel
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Sales\Model\Resource\Recurring\Profile\CollectionFactory $profileCollection
     * @param \Magento\Sales\Model\Recurring\ProfileFactory $recurringProfile
     * @param \Magento\Core\Model\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Core\Model\Url $urlModel,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Sales\Model\Resource\Recurring\Profile\CollectionFactory $profileCollection,
        \Magento\Sales\Model\Recurring\ProfileFactory $recurringProfile,
        \Magento\Core\Model\Registry $coreRegistry,
        array $data = array()
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct(
            $context,
            $urlModel,
            $backendHelper,
            $paymentData,
            $profileCollection,
            $recurringProfile,
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
        $customer = $this->_coreRegistry->registry('current_customer');
        return (bool)$customer->getId();
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
     * @return \Magento\Sales\Block\Adminhtml\Customer\Edit\Tab\Recurring\Profile
     */
    protected function _prepareCollection()
    {
        $collection = $this->_profileCollection->create()
            ->addFieldToFilter('customer_id', $this->_coreRegistry->registry('current_customer')->getId());
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
        return $this->getUrl('sales/recurring_profile/customerGrid', array('_current' => true));
    }
}
