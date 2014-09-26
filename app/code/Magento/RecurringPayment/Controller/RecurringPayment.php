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

use Magento\Framework\App\RequestInterface;
use Magento\Customer\Controller\RegistryConstants;

class RecurringPayment extends \Magento\Framework\App\Action\Action
{
    /**
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\App\Action\Title
     */
    protected $_title;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Action\Title $title
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Action\Title $title,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
        $this->_title = $title;
        $this->_customerSession = $customerSession;
    }

    /**
     * Make sure customer is logged in and put it into registry
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$request->isDispatched()) {
            return parent::dispatch($request);
        }
        if (!$this->_customerSession->authenticate($this)) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }
        $customer = $this->_customerSession->getCustomer();
        $this->_coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER, $customer);
        $this->_coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, $customer->getId());
        return parent::dispatch($request);
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
            $this->_view->loadLayout();
            $this->_view->getLayout()->initMessages();

            $title = __('Recurring Payment #%1', $payment->getReferenceId());
            $this->_view->getPage()->getConfig()->setTitle($title);

            $this->_view->renderLayout();
            return;
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }
        $this->_redirect('*/*/');
    }

    /**
     * Instantiate current payment and put it into registry
     *
     * @return \Magento\RecurringPayment\Model\Payment
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _initPayment()
    {
        $payment = $this->_objectManager->create(
            'Magento\RecurringPayment\Model\Payment'
        )->load(
            $this->getRequest()->getParam('payment')
        );
        if (!$payment->getId() || $payment->getCustomerId() != $this->_customerSession->getId()) {
            throw new \Magento\Framework\Model\Exception(__('We can\'t find the payment you specified.'));
        }
        $this->_coreRegistry->register('current_recurring_payment', $payment);
        return $payment;
    }
}
