<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\Framework\Exception\NoSuchEntityException;

class ResetPassword extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Reset password handler
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $customerId = (int)$this->getRequest()->getParam('customer_id', 0);
        if (!$customerId) {
            return $this->_redirect('customer/index');
        }

        try {
            $customer = $this->_customerRepository->getById($customerId);
            $this->customerAccountManagement->initiatePasswordReset(
                $customer->getEmail(),
                \Magento\Customer\Model\AccountManagement::EMAIL_REMINDER,
                $customer->getWebsiteId()
            );
            $this->messageManager->addSuccess(__('Customer will receive an email with a link to reset password.'));
        } catch (NoSuchEntityException $exception) {
            return $this->_redirect('customer/index');
        } catch (\Magento\Framework\Model\Exception $exception) {
            $messages = $exception->getMessages(\Magento\Framework\Message\MessageInterface::TYPE_ERROR);
            if (!count($messages)) {
                $messages = $exception->getMessage();
            }
            $this->_addSessionErrorMessages($messages);
        } catch (\Exception $exception) {
            $this->messageManager->addException(
                $exception,
                __('An error occurred while resetting customer password.')
            );
        }

        $this->_redirect('customer/*/edit', ['id' => $customerId, '_current' => true]);
    }
}
