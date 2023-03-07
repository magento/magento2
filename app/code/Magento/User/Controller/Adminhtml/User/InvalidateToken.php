<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Controller\Adminhtml\User;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Integration\Api\AdminTokenServiceInterface;
use Magento\User\Controller\Adminhtml\User;
use Magento\User\Model\UserFactory;

/**
 * Class InvalidateToken - used to invalidate/revoke all authentication tokens for a specific user.
 */
class InvalidateToken extends User
{
    /**
     * Inject dependencies.
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param UserFactory $userFactory
     * @param AdminTokenServiceInterface $tokenService
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        UserFactory $userFactory,
        protected readonly AdminTokenServiceInterface $tokenService
    ) {
        parent::__construct($context, $coreRegistry, $userFactory);
    }

    /**
     * @return void
     */
    public function execute()
    {
        if ($userId = $this->getRequest()->getParam('user_id')) {
            try {
                $this->tokenService->revokeAdminAccessToken($userId);
                $this->messageManager->addSuccess(__('You have revoked the user\'s tokens.'));
                $this->_redirect('adminhtml/*/edit', ['user_id' => $userId]);
                return;
            } catch (Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('adminhtml/*/edit', ['user_id' => $userId]);
                return;
            }
        }
        $this->messageManager->addError(__('We can\'t find a user to revoke.'));
        $this->_redirect('adminhtml/*');
    }
}
