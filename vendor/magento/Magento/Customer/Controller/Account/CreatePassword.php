<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Customer\Controller\Account;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;

class CreatePassword extends \Magento\Customer\Controller\Account
{
    /** @var AccountManagementInterface */
    protected $customerAccountManagement;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param AccountManagementInterface $customerAccountManagement
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement
    ) {
        $this->customerAccountManagement = $customerAccountManagement;
        parent::__construct($context, $customerSession);
    }

    /**
     * Resetting password handler
     *
     * @return void
     */
    public function execute()
    {
        $resetPasswordToken = (string)$this->getRequest()->getParam('token');
        $customerId = (int)$this->getRequest()->getParam('id');
        try {
            $this->customerAccountManagement->validateResetPasswordLinkToken($customerId, $resetPasswordToken);
            $this->_view->loadLayout();
            // Pass received parameters to the reset forgotten password form
            $this->_view->getLayout()->getBlock(
                'resetPassword'
            )->setCustomerId(
                $customerId
            )->setResetPasswordLinkToken(
                $resetPasswordToken
            );
            $this->_view->renderLayout();
        } catch (\Exception $exception) {
            $this->messageManager->addError(__('Your password reset link has expired.'));
            $this->_redirect('*/*/forgotpassword');
        }
    }
}
