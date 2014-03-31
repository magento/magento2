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
 * Recurring payments view/management controller
 */
namespace Magento\RecurringPayment\Controller;

use Magento\App\RequestInterface;
use Magento\Customer\Controller\RegistryConstants;

class RecurringPayment extends \Magento\App\Action\Action
{
    /**
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_session = null;

    /**
     * Core registry
     *
     * @var \Magento\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\App\Action\Title
     */
    protected $_title;

    /**
     * @param \Magento\App\Action\Context $context
     * @param \Magento\Registry $coreRegistry
     * @param \Magento\App\Action\Title $title
     */
    public function __construct(
        \Magento\App\Action\Context $context,
        \Magento\Registry $coreRegistry,
        \Magento\App\Action\Title $title
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
        $this->_title = $title;
    }

    /**
     * Make sure customer is logged in and put it into registry
     *
     * @param RequestInterface $request
     * @return \Magento\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$request->isDispatched()) {
            return parent::dispatch($request);
        }
        $this->_session = $this->_objectManager->get('Magento\Customer\Model\Session');
        if (!$this->_session->authenticate($this)) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }
        $customer = $this->_session->getCustomer();
        $this->_coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER, $customer);
        $this->_coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, $customer->getId());
        return parent::dispatch($request);
    }

    /**
     * Payments listing
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_title->add(__('Recurring Billing Payments'));
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }

    /**
     * Payment main view
     *
     * @return void
     */
    public function viewAction()
    {
        $this->_viewAction();
    }

    /**
     * Payment related orders view
     *
     * @return void
     */
    public function ordersAction()
    {
        $this->_viewAction();
    }

    /**
     * Attempt to set payment state
     *
     * @return void
     */
    public function updateStateAction()
    {
        $payment = null;
        try {
            $payment = $this->_initPayment();

            switch ($this->getRequest()->getParam('action')) {
                case 'cancel':
                    $payment->cancel();
                    break;
                case 'suspend':
                    $payment->suspend();
                    break;
                case 'activate':
                    $payment->activate();
                    break;
                default:
                    break;
            }
            $this->messageManager->addSuccess(__('The payment state has been updated.'));
        } catch (\Magento\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We couldn\'t update the payment.'));
            $this->_objectManager->get('Magento\Logger')->logException($e);
        }
        if ($payment) {
            $this->_redirect('*/*/view', array('payment' => $payment->getId()));
        } else {
            $this->_redirect('*/*/');
        }
    }

    /**
     * Fetch an update with payment
     *
     * @return void
     */
    public function updatePaymentAction()
    {
        $payment = null;
        try {
            $payment = $this->_initPayment();
            $payment->fetchUpdate();
            if ($payment->hasDataChanges()) {
                $payment->save();
                $this->messageManager->addSuccess(__('The payment has been updated.'));
            } else {
                $this->messageManager->addNotice(__('The payment has no changes.'));
            }
        } catch (\Magento\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We couldn\'t update the payment.'));
            $this->_objectManager->get('Magento\Logger')->logException($e);
        }
        if ($payment) {
            $this->_redirect('*/*/view', array('payment' => $payment->getId()));
        } else {
            $this->_redirect('*/*/');
        }
    }

    /**
     * Generic payment view action
     *
     * @return void
     */
    protected function _viewAction()
    {
        try {
            $payment = $this->_initPayment();
            $this->_title->add(__('Recurring Billing Payments'));
            $this->_title->add(__('Payment #%1', $payment->getReferenceId()));
            $this->_view->loadLayout();
            $this->_view->getLayout()->initMessages();
            $this->_view->renderLayout();
            return;
        } catch (\Magento\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Logger')->logException($e);
        }
        $this->_redirect('*/*/');
    }

    /**
     * Instantiate current payment and put it into registry
     *
     * @return \Magento\RecurringPayment\Model\Payment
     * @throws \Magento\Model\Exception
     */
    protected function _initPayment()
    {
        $payment = $this->_objectManager->create(
            'Magento\RecurringPayment\Model\Payment'
        )->load(
            $this->getRequest()->getParam('payment')
        );
        if (!$payment->getId()) {
            throw new \Magento\Model\Exception(__('We can\'t find the payment you specified.'));
        }
        $this->_coreRegistry->register('current_recurring_payment', $payment);
        return $payment;
    }
}
