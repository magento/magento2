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

use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerDataBuilder;
use Magento\Core\App\Action\FormKeyValidator;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\AuthenticationException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditPost extends \Magento\Customer\Controller\Account
{
    /** @var AccountManagementInterface */
    protected $customerAccountManagement;

    /** @var CustomerRepositoryInterface  */
    protected $customerRepository;

    /** @var CustomerDataBuilder */
    protected $customerDataBuilder;

    /** @var FormKeyValidator */
    protected $formKeyValidator;

    /** @var CustomerExtractor */
    protected $customerExtractor;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param AccountManagementInterface $customerAccountManagement
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerDataBuilder $customerDataBuilder
     * @param FormKeyValidator $formKeyValidator
     * @param CustomerExtractor $customerExtractor
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        CustomerRepositoryInterface $customerRepository,
        CustomerDataBuilder $customerDataBuilder,
        FormKeyValidator $formKeyValidator,
        CustomerExtractor $customerExtractor
    ) {
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerRepository = $customerRepository;
        $this->customerDataBuilder = $customerDataBuilder;
        $this->formKeyValidator = $formKeyValidator;
        $this->customerExtractor = $customerExtractor;
        parent::__construct($context, $customerSession);
    }

    /**
     * Change customer password action
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $this->_redirect('*/*/edit');
            return;
        }

        if ($this->getRequest()->isPost()) {
            $customerId = $this->_getSession()->getCustomerId();
            $customer = $this->customerExtractor->extract('customer_account_edit', $this->_request);
            $this->customerDataBuilder->populate($customer);
            $this->customerDataBuilder->setId($customerId);

            if ($this->getRequest()->getParam('change_password')) {
                $currPass = $this->getRequest()->getPost('current_password');
                $newPass = $this->getRequest()->getPost('password');
                $confPass = $this->getRequest()->getPost('password_confirmation');

                if (strlen($newPass)) {
                    if ($newPass == $confPass) {
                        try {
                            $customerEmail = $this->customerRepository->getById($customerId)->getEmail();
                            $this->customerAccountManagement->changePassword($customerEmail, $currPass, $newPass);
                        } catch (AuthenticationException $e) {
                            $this->messageManager->addError($e->getMessage());
                        } catch (\Exception $e) {
                            $this->messageManager->addException(
                                $e,
                                __('A problem was encountered trying to change password.')
                            );
                        }
                    } else {
                        $this->messageManager->addError(__('Confirm your new password'));
                    }
                } else {
                    $this->messageManager->addError(__('New password field cannot be empty.'));
                }
            }

            try {
                $this->customerRepository->save($this->customerDataBuilder->create());
            } catch (AuthenticationException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (InputException $e) {
                $this->messageManager->addException($e, __('Invalid input'));
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __('Cannot save the customer.') . $e->getMessage() . '<pre>' . $e->getTraceAsString() . '</pre>'
                );
            }

            if ($this->messageManager->getMessages()->getCount() > 0) {
                $this->_getSession()->setCustomerFormData($this->getRequest()->getPost());
                $this->_redirect('*/*/edit');
                return;
            }

            $this->messageManager->addSuccess(__('The account information has been saved.'));
            $this->_redirect('customer/account');
            return;
        }

        $this->_redirect('*/*/edit');
    }
}
