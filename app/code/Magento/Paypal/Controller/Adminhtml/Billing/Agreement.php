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
namespace Magento\Paypal\Controller\Adminhtml\Billing;

/**
 * Adminhtml billing agreement controller
 */
class Agreement extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Billing agreements
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_title->add(__('Billing Agreements'));

        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Paypal::paypal_billing_agreement');
        $this->_view->renderLayout();
    }

    /**
     * Ajax action for billing agreements
     *
     * @return void
     */
    public function gridAction()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }

    /**
     * View billing agreement action
     *
     * @return void
     */
    public function viewAction()
    {
        $agreementModel = $this->_initBillingAgreement();

        if ($agreementModel) {
            $this->_title->add(__('Billing Agreements'));
            $this->_title->add(sprintf("#%s", $agreementModel->getReferenceId()));

            $this->_view->loadLayout();
            $this->_setActiveMenu('Magento_Paypal::paypal_billing_agreement');
            $this->_view->renderLayout();
            return;
        }

        $this->_redirect('paypal/*/');
        return;
    }

    /**
     * Related orders ajax action
     *
     * @return void
     */
    public function ordersGridAction()
    {
        $this->_initBillingAgreement();
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }

    /**
     * Customer billing agreements ajax action
     *
     * @return void
     */
    public function customerGridAction()
    {
        $this->_initCustomer();
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }

    /**
     * Cancel billing agreement action
     *
     * @return void
     */
    public function cancelAction()
    {
        $agreementModel = $this->_initBillingAgreement();

        if ($agreementModel && $agreementModel->canCancel()) {
            try {
                $agreementModel->cancel();
                $this->messageManager->addSuccess(__('You canceled the billing agreement.'));
                $this->_redirect('paypal/*/view', array('_current' => true));
                return;
            } catch (\Magento\Core\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(__('We could not cancel the billing agreement.'));
                $this->_objectManager->get('Magento\Logger')->logException($e);
            }
            $this->_redirect('paypal/*/view', array('_current' => true));
        }
        return $this->_redirect('paypal/*/');
    }

    /**
     * Delete billing agreement action
     *
     * @return void
     */
    public function deleteAction()
    {
        $agreementModel = $this->_initBillingAgreement();

        if ($agreementModel) {
            try {
                $agreementModel->delete();
                $this->messageManager->addSuccess(__('You deleted the billing agreement.'));
                $this->_redirect('paypal/*/');
                return;
            } catch (\Magento\Core\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(__('We could not delete the billing agreement.'));
                $this->_objectManager->get('Magento\Logger')->logException($e);
            }
            $this->_redirect('paypal/*/view', array('_current' => true));
        }
        $this->_redirect('paypal/*/');
    }

    /**
     * Initialize billing agreement by ID specified in request
     *
     * @return \Magento\Paypal\Model\Billing\Agreement|false
     */
    protected function _initBillingAgreement()
    {
        $agreementId = $this->getRequest()->getParam('agreement');
        $agreementModel = $this->_objectManager->create('Magento\Paypal\Model\Billing\Agreement')->load($agreementId);

        if (!$agreementModel->getId()) {
            $this->messageManager->addError(__('Please specify the correct billing agreement ID and try again.'));
            return false;
        }

        $this->_coreRegistry->register('current_billing_agreement', $agreementModel);
        return $agreementModel;
    }

    /**
     * Initialize customer by ID specified in request
     *
     * @return $this
     */
    protected function _initCustomer()
    {
        $customerId = (int)$this->getRequest()->getParam('id');
        if ($customerId) {
            $this->_coreRegistry->register('current_customer_id', $customerId);
        }
        return $this;
    }

    /**
     * Check currently called action by permissions for current user
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'index':
            case 'grid' :
            case 'view' :
                return $this->_authorization->isAllowed('Magento_Paypal::billing_agreement_actions_view');
            case 'cancel':
            case 'delete':
                return $this->_authorization->isAllowed('Magento_Paypal::actions_manage');
            default:
                return $this->_authorization->isAllowed('Magento_Paypal::billing_agreement');
        }
    }
}
