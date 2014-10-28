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
namespace Magento\Paypal\Block\Adminhtml\Customer\Edit\Tab;

use Magento\Customer\Controller\RegistryConstants;

/**
 * Adminhtml customer billing agreement tab
 */
class Agreement extends \Magento\Paypal\Block\Adminhtml\Billing\Agreement\Grid implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Columns, that should be removed from grid
     *
     * @var array
     */
    protected $_columnsToRemove = array('customer_email', 'customer_firstname', 'customer_lastname');

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Paypal\Helper\Data $helper
     * @param \Magento\Paypal\Model\Resource\Billing\Agreement\CollectionFactory $agreementFactory
     * @param \Magento\Paypal\Model\Billing\Agreement $agreementModel
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Paypal\Helper\Data $helper,
        \Magento\Paypal\Model\Resource\Billing\Agreement\CollectionFactory $agreementFactory,
        \Magento\Paypal\Model\Billing\Agreement $agreementModel,
        \Magento\Framework\Registry $coreRegistry,
        array $data = array()
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $backendHelper, $helper, $agreementFactory, $agreementModel, $data);
    }

    /**
     * Disable filters and paging
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('customer_edit_tab_agreements');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Billing Agreements');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Billing Agreements');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return !is_null($this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID));
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('paypal/billing_agreement/customerGrid', array('_current' => true));
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
     * Prepare collection for grid
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $customerId = $this->_coreRegistry->registry('current_customer_id');
        if (!$customerId) {
            $customerId = $this->_coreRegistry->registry('current_customer')->getId();
        }
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
