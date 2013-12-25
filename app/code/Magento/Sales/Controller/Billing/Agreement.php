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
 * Billing agreements controller
 */
namespace Magento\Sales\Controller\Billing;

use Magento\App\Action\NotFoundException;
use Magento\App\RequestInterface;

class Agreement extends \Magento\App\Action\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\App\Action\Title
     */
    protected $_title;

    /**
     * @param \Magento\App\Action\Context $context
     * @param \Magento\Core\Model\Registry $coreRegistry
     * @param \Magento\App\Action\Title $title
     */
    public function __construct(
        \Magento\App\Action\Context $context,
        \Magento\Core\Model\Registry $coreRegistry,
        \Magento\App\Action\Title $title
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
        $this->_title = $title;
    }

    /**
     * View billing agreements
     *
     */
    public function indexAction()
    {
        $this->_title->add(__('Billing Agreements'));
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }

    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return \Magento\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$request->isDispatched()) {
            return parent::dispatch($request);
        }
        if (!$this->_getSession()->authenticate($this)) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }
        return parent::dispatch($request);
    }

    /**
     * View billing agreement
     *
     */
    public function viewAction()
    {
        if (!$agreement = $this->_initAgreement()) {
            return;
        }
        $this->_title->add(__('Billing Agreements'));
        $this->_title->add(__('Billing Agreement # %1', $agreement->getReferenceId()));
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $navigationBlock = $this->_view->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('sales/billing_agreement/');
        }
        $this->_view->renderLayout();
    }

    /**
     * Wizard start action
     *
     */
    public function startWizardAction()
    {
        $agreement = $this->_objectManager->create('Magento\Sales\Model\Billing\Agreement');
        $paymentCode = $this->getRequest()->getParam('payment_method');
        if ($paymentCode) {
            try {
                $agreement
                    ->setStoreId($this->_objectManager->get('Magento\Core\Model\StoreManager')->getStore()->getId())
                    ->setMethodCode($paymentCode)
                    ->setReturnUrl($this->_objectManager->create('Magento\Core\Model\Url')
                        ->getUrl('*/*/returnWizard', array('payment_method' => $paymentCode)))
                    ->setCancelUrl($this->_objectManager->create('Magento\Core\Model\Url')
                        ->getUrl('*/*/cancelWizard', array('payment_method' => $paymentCode)));

                return $this->getResponse()->setRedirect($agreement->initToken());
            } catch (\Magento\Core\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->_objectManager->get('Magento\Logger')->logException($e);
                $this->messageManager->addError(__('We couldn\'t start the billing agreement wizard.'));
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Wizard return action
     *
     */
    public function returnWizardAction()
    {
        $agreement = $this->_objectManager->create('Magento\Sales\Model\Billing\Agreement');
        $paymentCode = $this->getRequest()->getParam('payment_method');
        $token = $this->getRequest()->getParam('token');
        if ($token && $paymentCode) {
            try {
                $agreement
                    ->setStoreId($this->_objectManager->get('Magento\Core\Model\StoreManager')->getStore()->getId())
                    ->setToken($token)
                    ->setMethodCode($paymentCode)
                    ->setCustomer($this->_objectManager->get('Magento\Customer\Model\Session')->getCustomer())
                    ->place();
                $this->messageManager->addSuccess(
                    __('The billing agreement "%1" has been created.', $agreement->getReferenceId())
                );
                $this->_redirect('*/*/view', array('agreement' => $agreement->getId()));
                return;
            } catch (\Magento\Core\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->_objectManager->get('Magento\Logger')->logException($e);
                $this->messageManager->addError(__('We couldn\'t finish the billing agreement wizard.'));
            }
            $this->_redirect('*/*/index');
        }
    }

    /**
     * Wizard cancel action
     *
     */
    public function cancelWizardAction()
    {
        $this->_redirect('*/*/index');
    }

    /**
     * Cancel action
     * Set billing agreement status to 'Canceled'
     *
     */
    public function cancelAction()
    {
        $agreement = $this->_initAgreement();
        if ($agreement && $agreement->canCancel()) {
            try {
                $agreement->cancel();
                $this->messageManager->addNotice(
                    __('The billing agreement "%1" has been canceled.', $agreement->getReferenceId())
                );
            } catch (\Magento\Core\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->_objectManager->get('Magento\Logger')->logException($e);
                $this->messageManager->addError(__('We couldn\'t cancel the billing agreement.'));
            }
        }
        $this->_redirect('*/*/view', array('_current' => true));
    }

    /**
     * Init billing agreement model from request
     *
     * @return \Magento\Sales\Model\Billing\Agreement
     */
    protected function _initAgreement()
    {
        $agreementId = $this->getRequest()->getParam('agreement');
        if ($agreementId) {
            $billingAgreement = $this->_objectManager->create('Magento\Sales\Model\Billing\Agreement')
                ->load($agreementId);
            if (!$billingAgreement->getAgreementId()) {
                $this->messageManager->addError(__('Please specify the correct billing agreement ID and try again.'));
                $this->_redirect('*/*/');
                return false;
            }
        }
        $this->_coreRegistry->register('current_billing_agreement', $billingAgreement);
        return $billingAgreement;
    }

    /**
     * Retrieve customer session model
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->_objectManager->get('Magento\Customer\Model\Session');
    }
}
