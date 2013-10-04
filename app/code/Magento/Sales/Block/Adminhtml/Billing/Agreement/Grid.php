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
 * Adminhtml billing agreements grid
 *
 * @author Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Sales\Block\Adminhtml\Billing\Agreement;

class Grid extends \Magento\Adminhtml\Block\Widget\Grid
{
    /**
     * Payment data
     *
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentData = null;

    /**
     * @var \Magento\Sales\Model\Resource\Billing\Agreement\CollectionFactory
     */
    protected $_agreementFactory;

    /**
     * @var \Magento\Sales\Model\Billing\Agreement
     */
    protected $_agreementModel;

    /**
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\Model\Url $urlModel
     * @param \Magento\Sales\Model\Resource\Billing\Agreement\CollectionFactory $agreementFactory
     * @param \Magento\Sales\Model\Billing\Agreement $agreementModel
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Core\Model\Url $urlModel,
        \Magento\Sales\Model\Resource\Billing\Agreement\CollectionFactory $agreementFactory,
        \Magento\Sales\Model\Billing\Agreement $agreementModel,
        array $data = array()
    ) {
        $this->_paymentData = $paymentData;
        $this->_agreementFactory = $agreementFactory;
        $this->_agreementModel = $agreementModel;
        parent::__construct($coreData, $context, $storeManager, $urlModel, $data);
    }

    /**
     * Set grid params
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('billing_agreements');
        $this->setUseAjax(true);
        $this->setDefaultSort('agreement_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Retrieve grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/sales_billing_agreement/grid', array('_current' => true));
    }

    /**
     * Retrieve row url
     *
     * @param object $item
     * @return string
     */
    public function getRowUrl($item)
    {
        return $this->getUrl('*/sales_billing_agreement/view', array('agreement' => $item->getAgreementId()));
    }

    /**
     * Prepare collection for grid
     *
     * @return \Magento\Adminhtml\Block\Widget\Grid
     */
    protected function _prepareCollection()
    {
        /** @var \Magento\Sales\Model\Resource\Billing\Agreement\Collection $collection */
        $collection = $this->_agreementFactory->create()
            ->addCustomerDetails();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Add columns to grid
     *
     * @return \Magento\Adminhtml\Block\Widget\Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('agreement_id', array(
            'header'            => __('ID'),
            'index'             => 'agreement_id',
            'type'              => 'text',
            'header_css_class'  => 'col-id',
            'column_css_class'  => 'col-id'
        ));

        $this->addColumn('customer_email', array(
            'header'            => __('Email'),
            'index'             => 'customer_email',
            'type'              => 'text',
            'header_css_class'  => 'col-mail',
            'column_css_class'  => 'col-mail'
        ));

        $this->addColumn('customer_firstname', array(
            'header'            => __('First Name'),
            'index'             => 'customer_firstname',
            'type'              => 'text',
            'escape'            => true,
            'header_css_class'  => 'col-name',
            'column_css_class'  => 'col-name'
        ));

        $this->addColumn('customer_lastname', array(
            'header'            => __('Last Name'),
            'index'             => 'customer_lastname',
            'type'              => 'text',
            'escape'            => true,
            'header_css_class'  => 'col-last-name',
            'column_css_class'  => 'col-last-name'
        ));

        $this->addColumn('method_code', array(
            'header'            => __('Payment Method'),
            'index'             => 'method_code',
            'type'              => 'options',
            'options'           => $this->_paymentData->getAllBillingAgreementMethods(),
            'header_css_class'  => 'col-payment',
            'column_css_class'  => 'col-payment'
        ));

        $this->addColumn('reference_id', array(
            'header'            => __('Reference ID'),
            'index'             => 'reference_id',
            'type'              => 'text',
            'header_css_class'  => 'col-reference',
            'column_css_class'  => 'col-reference'
        ));

        $this->addColumn('status', array(
            'header'            => __('Status'),
            'index'             => 'status',
            'type'              => 'options',
            'options'           => $this->_agreementModel->getStatusesArray(),
            'header_css_class'  => 'col-status',
            'column_css_class'  => 'col-status'
        ));

        $this->addColumn('created_at', array(
            'header'            => __('Created'),
            'index'             => 'agreement_created_at',
            'type'              => 'datetime',
            'align'             => 'center',
            'default'           => __('N/A'),
            'html_decorators'   => array('nobr'),
            'header_css_class'  => 'col-period',
            'column_css_class'  => 'col-period'
        ));

        $this->addColumn('updated_at', array(
            'header'            => __('Updated'),
            'index'             => 'agreement_updated_at',
            'type'              => 'datetime',
            'align'             => 'center',
            'default'           => __('N/A'),
            'html_decorators'   => array('nobr'),
            'header_css_class'  => 'col-period',
            'column_css_class'  => 'col-period'
        ));

        return parent::_prepareColumns();
    }
}
