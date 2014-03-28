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
namespace Magento\RecurringPayment\Controller\Adminhtml;

use Magento\App\Action\NotFoundException;
use Magento\Model\Exception as CoreException;
use Magento\Customer\Controller\RegistryConstants;

/**
 * Recurring payments view/management controller
 *
 * TODO: implement ACL restrictions
 */
class RecurringPayment extends \Magento\Backend\App\Action
{
    /**#@+
     * Request parameter key
     */
    const PARAM_CUSTOMER_ID = 'id';

    const PARAM_PAYMENT = 'payment';

    const PARAM_ACTION = 'action';

    /**#@-*/

    /**#@+
     * Value for PARAM_ACTION request parameter
     */
    const ACTION_CANCEL = 'cancel';

    const ACTION_SUSPEND = 'suspend';

    const ACTION_ACTIVATE = 'activate';

    /**#@-*/

    /**
     * Core registry
     *
     * @var \Magento\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Logger
     */
    protected $_logger;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Registry $coreRegistry
     * @param \Magento\Logger $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Registry $coreRegistry,
        \Magento\Logger $logger
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_logger = $logger;
        parent::__construct($context);
    }

    /**
     * Recurring payments list
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_title->add(__('Recurring Billing Payments'));
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_RecurringPayment::recurring_payment');
        $this->_view->renderLayout();
    }

    /**
     * View recurring payment details
     *
     * @return void
     */
    public function viewAction()
    {
        try {
            $this->_title->add(__('Recurring Billing Payments'));
            $payment = $this->_initPayment();
            $this->_view->loadLayout();
            $this->_setActiveMenu('Magento_RecurringPayment::recurring_payment');
            $this->_title->add(__('Payment #%1', $payment->getReferenceId()));
            $this->_view->renderLayout();
            return;
        } catch (CoreException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_logger->logException($e);
        }
        $this->_redirect('sales/*/');
    }

    /**
     * Payments ajax grid
     *
     * @return void
     */
    public function gridAction()
    {
        try {
            $this->_view->loadLayout()->renderLayout();
            return;
        } catch (CoreException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_logger->logException($e);
        }
        $this->_redirect('sales/*/');
    }

    /**
     * Payment orders ajax grid
     *
     * @return void
     * @throws NotFoundException
     */
    public function ordersAction()
    {
        try {
            $this->_initPayment();
            $this->_view->loadLayout()->renderLayout();
        } catch (\Exception $e) {
            $this->_logger->logException($e);
            throw new NotFoundException();
        }
    }

    /**
     * Payment state updater action
     *
     * @return void
     */
    public function updateStateAction()
    {
        $payment = null;
        try {
            $payment = $this->_initPayment();
            $action = $this->getRequest()->getParam(self::PARAM_ACTION);

            switch ($action) {
                case self::ACTION_CANCEL:
                    $payment->cancel();
                    break;
                case self::ACTION_SUSPEND:
                    $payment->suspend();
                    break;
                case self::ACTION_ACTIVATE:
                    $payment->activate();
                    break;
                default:
                    throw new \Exception(sprintf('Wrong action parameter: %s', $action));
            }
            $this->messageManager->addSuccess(__('The payment state has been updated.'));
        } catch (CoreException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We could not update the payment.'));
            $this->_logger->logException($e);
        }
        if ($payment) {
            $this->_redirect('sales/*/view', array(self::PARAM_PAYMENT => $payment->getId()));
        } else {
            $this->_redirect('sales/*/');
        }
    }

    /**
     * Payment information updater action
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
                $this->messageManager->addSuccess(__('You updated the payment.'));
            } else {
                $this->messageManager->addNotice(__('The payment has no changes.'));
            }
        } catch (CoreException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We could not update the payment.'));
            $this->_logger->logException($e);
        }
        if ($payment) {
            $this->_redirect('sales/*/view', array(self::PARAM_PAYMENT => $payment->getId()));
        } else {
            $this->_redirect('sales/*/');
        }
    }

    /**
     * Customer grid ajax action
     *
     * @return void
     */
    public function customerGridAction()
    {
        $customerId = (int)$this->getRequest()->getParam(self::PARAM_CUSTOMER_ID);

        if ($customerId) {
            $this->_coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, $customerId);
        }

        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }

    /**
     * Load/set payment
     *
     * @return \Magento\RecurringPayment\Model\Payment
     * @throws \Magento\Model\Exception
     */
    protected function _initPayment()
    {
        $payment = $this->_objectManager->create(
            'Magento\RecurringPayment\Model\Payment'
        )->load(
            $this->getRequest()->getParam(self::PARAM_PAYMENT)
        );
        if (!$payment->getId()) {
            throw new CoreException(__('The payment you specified does not exist.'));
        }
        $this->_coreRegistry->register('current_recurring_payment', $payment);
        return $payment;
    }
}
