<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\User\Controller\Adminhtml\User;

/**
 * Class InvalidateToken - used to invalidate/revoke all authentication tokens for a specific user.
 */
class InvalidateToken extends \Magento\User\Controller\Adminhtml\User
{
    /**
     * @return void
     */
    public function execute()
    {
        if ($userId = $this->getRequest()->getParam('user_id')) {
            /** @var \Magento\Integration\Service\V1\AdminTokenService $tokenService */
            $tokenService = $this->_objectManager->get('Magento\Integration\Service\V1\AdminTokenService');
            try {
                $tokenService->revokeAdminAccessToken($userId);
                $this->messageManager->addSuccess(__('You have revoked the user\'s tokens.'));
                $this->_redirect('adminhtml/*/edit', ['user_id' => $userId]);
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('adminhtml/*/edit', ['user_id' => $userId]);
                return;
            }
        }
        $this->messageManager->addError(__('We can\'t find a user to revoke.'));
        $this->_redirect('adminhtml/*');
    }
}
