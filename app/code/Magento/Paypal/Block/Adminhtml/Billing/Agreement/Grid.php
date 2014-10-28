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
namespace Magento\Paypal\Block\Adminhtml\Billing\Agreement;

/**
 * Adminhtml billing agreements grid
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Paypal\Helper\Data
     */
    protected $_helper = null;

    /**
     * @var \Magento\Paypal\Model\Resource\Billing\Agreement\CollectionFactory
     */
    protected $_agreementFactory;

    /**
     * @var \Magento\Paypal\Model\Billing\Agreement
     */
    protected $_agreementModel;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Paypal\Helper\Data $helper
     * @param \Magento\Paypal\Model\Resource\Billing\Agreement\CollectionFactory $agreementFactory
     * @param \Magento\Paypal\Model\Billing\Agreement $agreementModel
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Paypal\Helper\Data $helper,
        \Magento\Paypal\Model\Resource\Billing\Agreement\CollectionFactory $agreementFactory,
        \Magento\Paypal\Model\Billing\Agreement $agreementModel,
        array $data = array()
    ) {
        $this->_helper = $helper;
        $this->_agreementFactory = $agreementFactory;
        $this->_agreementModel = $agreementModel;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Set grid params
     *
     * @return void
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
        return $this->getUrl('paypal/billing_agreement/grid', array('_current' => true));
    }

    /**
     * Retrieve row url
     *
     * @param object $item
     * @return string
     */
    public function getRowUrl($item)
    {
        return $this->getUrl('paypal/billing_agreement/view', array('agreement' => $item->getAgreementId()));
    }

    /**
     * Prepare collection for grid
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        /** @var \Magento\Paypal\Model\Resource\Billing\Agreement\Collection $collection */
        $collection = $this->_agreementFactory->create()->addCustomerDetails();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Add columns to grid
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'agreement_id',
            array(
                'header' => __('ID'),
                'index' => 'agreement_id',
                'type' => 'text',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            )
        );

        $this->addColumn(
            'customer_email',
            array(
                'header' => __('Email'),
                'index' => 'customer_email',
                'type' => 'text',
                'header_css_class' => 'col-mail',
                'column_css_class' => 'col-mail'
            )
        );

        $this->addColumn(
            'customer_firstname',
            array(
                'header' => __('First Name'),
                'index' => 'customer_firstname',
                'type' => 'text',
                'escape' => true,
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            )
        );

        $this->addColumn(
            'customer_lastname',
            array(
                'header' => __('Last Name'),
                'index' => 'customer_lastname',
                'type' => 'text',
                'escape' => true,
                'header_css_class' => 'col-last-name',
                'column_css_class' => 'col-last-name'
            )
        );

        $this->addColumn(
            'reference_id',
            array(
                'header' => __('Reference ID'),
                'index' => 'reference_id',
                'type' => 'text',
                'header_css_class' => 'col-reference',
                'column_css_class' => 'col-reference'
            )
        );

        $this->addColumn(
            'status',
            array(
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => $this->_agreementModel->getStatusesArray(),
                'header_css_class' => 'col-status',
                'column_css_class' => 'col-status'
            )
        );

        $this->addColumn(
            'created_at',
            array(
                'header' => __('Created'),
                'index' => 'created_at',
                'type' => 'datetime',
                'align' => 'center',
                'default' => __('N/A'),
                'html_decorators' => array('nobr'),
                'header_css_class' => 'col-period',
                'column_css_class' => 'col-period'
            )
        );

        $this->addColumn(
            'updated_at',
            array(
                'header' => __('Updated'),
                'index' => 'updated_at',
                'type' => 'datetime',
                'align' => 'center',
                'default' => __('N/A'),
                'html_decorators' => array('nobr'),
                'header_css_class' => 'col-period',
                'column_css_class' => 'col-period'
            )
        );

        return parent::_prepareColumns();
    }
}
