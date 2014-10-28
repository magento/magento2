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
namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\Customer\Service\V1\CustomerAccountServiceInterface;
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
            $customer = $this->_customerAccountService->getCustomer($customerId);
            $this->_customerAccountService->initiatePasswordReset(
                $customer->getEmail(),
                CustomerAccountServiceInterface::EMAIL_REMINDER,
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

        $this->_redirect('customer/*/edit', array('id' => $customerId, '_current' => true));
    }
}
