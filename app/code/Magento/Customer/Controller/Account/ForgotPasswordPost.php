<?php
/**
 *
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
namespace Magento\Customer\Controller\Account;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Service\V1\CustomerAccountServiceInterface;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Customer\Helper\Address;
use Magento\Framework\UrlFactory;
use Magento\Framework\StoreManagerInterface;

class ForgotPasswordPost extends \Magento\Customer\Controller\Account
{
    /** @var \Magento\Framework\Escaper */
    protected $escaper;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param Address $addressHelper
     * @param UrlFactory $urlFactory
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param CustomerAccountServiceInterface $customerAccountService
     * @param \Magento\Framework\Escaper $escaper
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        Address $addressHelper,
        UrlFactory $urlFactory,
        StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        CustomerAccountServiceInterface $customerAccountService,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->escaper = $escaper;
        parent::__construct(
            $context,
            $customerSession,
            $addressHelper,
            $urlFactory,
            $storeManager,
            $scopeConfig,
            $customerAccountService
        );
    }

    /**
     * Forgot customer password action
     *
     * @return void
     */
    public function execute()
    {
        $email = (string)$this->getRequest()->getPost('email');
        if ($email) {
            if (!\Zend_Validate::is($email, 'EmailAddress')) {
                $this->_getSession()->setForgottenEmail($email);
                $this->messageManager->addError(__('Please correct the email address.'));
                $this->_redirect('*/*/forgotpassword');
                return;
            }

            try {
                $this->_customerAccountService->initiatePasswordReset(
                    $email,
                    CustomerAccountServiceInterface::EMAIL_RESET
                );
            } catch (NoSuchEntityException $e) {
                // Do nothing, we don't want anyone to use this action to determine which email accounts are registered.
            } catch (\Exception $exception) {
                $this->messageManager->addException($exception, __('Unable to send password reset email.'));
                $this->_redirect('*/*/forgotpassword');
                return;
            }
            $email = $this->escaper->escapeHtml($email);
            // @codingStandardsIgnoreStart
            $this->messageManager->addSuccess(
                __(
                    'If there is an account associated with %1 you will receive an email with a link to reset your password.',
                    $email
                )
            );
            // @codingStandardsIgnoreEnd
            $this->_redirect('*/*/');
            return;
        } else {
            $this->messageManager->addError(__('Please enter your email.'));
            $this->_redirect('*/*/forgotpassword');
            return;
        }
    }
}
