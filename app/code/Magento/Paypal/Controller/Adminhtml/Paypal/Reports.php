<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Paypal\Controller\Adminhtml\Paypal;

/**
 * PayPal Settlement Reports Controller
 */
class Reports extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Paypal\Model\Report\Settlement\RowFactory
     */
    protected $_rowFactory;

    /**
     * @var \Magento\Paypal\Model\Report\SettlementFactory
     */
    protected $_settlementFactory;

    /**
     * @var \Magento\Framework\Logger
     */
    protected $_logger;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Paypal\Model\Report\Settlement\RowFactory $rowFactory
     * @param \Magento\Paypal\Model\Report\SettlementFactory $settlementFactory
     * @param \Magento\Framework\Logger $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Paypal\Model\Report\Settlement\RowFactory $rowFactory,
        \Magento\Paypal\Model\Report\SettlementFactory $settlementFactory,
        \Magento\Framework\Logger $logger
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_rowFactory = $rowFactory;
        $this->_settlementFactory = $settlementFactory;
        $this->_logger = $logger;
        parent::__construct($context);
    }

    /**
     * Initialize titles, navigation
     *
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Magento_Paypal::report_salesroot_paypal_settlement_reports'
        )->_addBreadcrumb(
            __('Reports'),
            __('Reports')
        )->_addBreadcrumb(
            __('Sales'),
            __('Sales')
        )->_addBreadcrumb(
            __('PayPal Settlement Reports'),
            __('PayPal Settlement Reports')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('PayPal Settlement Reports'));
        return $this;
    }

    /**
     * ACL check
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'index':
            case 'details':
                return $this->_authorization->isAllowed('Magento_Paypal::paypal_settlement_reports_view');
            case 'fetch':
                return $this->_authorization->isAllowed('Magento_Paypal::fetch');
            default:
                return $this->_authorization->isAllowed('Magento_Paypal::paypal_settlement_reports');
        }
    }
}
