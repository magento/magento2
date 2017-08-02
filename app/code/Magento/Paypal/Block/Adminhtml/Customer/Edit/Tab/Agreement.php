<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Adminhtml\Customer\Edit\Tab;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Ui\Component\Layout\Tabs\TabInterface;

/**
 * Adminhtml customer billing agreement tab
 *
 * @api
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since 2.0.0
 */
class Agreement extends \Magento\Paypal\Block\Adminhtml\Billing\Agreement\Grid implements TabInterface
{
    /**
     * Columns, that should be removed from grid
     *
     * @var array
     * @since 2.0.0
     */
    protected $_columnsToRemove = ['customer_email', 'customer_firstname', 'customer_lastname'];

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Paypal\Helper\Data $helper
     * @param \Magento\Paypal\Model\ResourceModel\Billing\Agreement\CollectionFactory $agreementFactory
     * @param \Magento\Paypal\Model\Billing\Agreement $agreementModel
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Paypal\Helper\Data $helper,
        \Magento\Paypal\Model\ResourceModel\Billing\Agreement\CollectionFactory $agreementFactory,
        \Magento\Paypal\Model\Billing\Agreement $agreementModel,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $backendHelper, $helper, $agreementFactory, $agreementModel, $data);
    }

    /**
     * Disable filters and paging
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('customer_edit_tab_agreements');
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getTabLabel()
    {
        return __('Billing Agreements');
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getTabTitle()
    {
        return __('Billing Agreements');
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function canShowTab()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID) !== null;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Get grid url
     *
     * @return string
     * @since 2.0.0
     */
    public function getGridUrl()
    {
        return $this->getUrl('paypal/billing_agreement/customerGrid', ['_current' => true]);
    }

    /**
     * Tab class getter
     *
     * @return string
     * @since 2.0.0
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     * @since 2.0.0
     */
    public function getTabUrl()
    {
        return '';
    }

    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     * @since 2.0.0
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * Defines after which tab, this tab should be rendered
     *
     * @return string
     * @since 2.0.0
     */
    public function getAfter()
    {
        return 'orders';
    }

    /**
     * Prepare collection for grid
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareCollection()
    {
        $customerId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        $collection = $this->_agreementFactory->create()->addFieldToFilter(
            'customer_id',
            $customerId
        )->setOrder(
            'created_at'
        );
        $this->setCollection($collection);
        return \Magento\Backend\Block\Widget\Grid::_prepareCollection();
    }

    /**
     * Remove some columns and make other not sortable
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @since 2.0.0
     */
    protected function _prepareColumns()
    {
        $result = parent::_prepareColumns();

        foreach ($this->getColumns() as $key => $value) {
            if (in_array($key, $this->_columnsToRemove)) {
                $this->removeColumn($key);
            }
        }
        return $result;
    }
}
