<?php
/************************************************************************
 *
 * Copyright 2025 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\User\Controller\Adminhtml\User;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;
use Magento\Integration\Api\AdminTokenServiceInterface;
use Magento\User\Controller\Adminhtml\User;
use Magento\User\Model\UserFactory;
use Magento\User\Helper\ForceSignIn;

/**
 * Class InvalidateToken - used to invalidate/revoke all authentication tokens for a specific user.
 */
class InvalidateToken extends User implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * @var AdminTokenServiceInterface
     */
    protected $tokenService;

    /**
     * @var ForceSignIn
     */
    private ForceSignIn $forceSignIn;

    /**
     * Inject dependencies.
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param UserFactory $userFactory
     * @param AdminTokenServiceInterface $tokenService
     * @param ForceSignIn|null $forceSignIn
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        UserFactory $userFactory,
        AdminTokenServiceInterface $tokenService,
        ?ForceSignIn $forceSignIn = null
    ) {
        parent::__construct($context, $coreRegistry, $userFactory);
        $this->tokenService = $tokenService;
        $this->forceSignIn = $forceSignIn ?: ObjectManager::getInstance()->get(ForceSignIn::class);
    }

    /**
     * Revoke admin token
     *
     * @return void
     */
    public function execute()
    {
        if ($userId = $this->getRequest()->getParam('user_id')) {
            try {
                $this->tokenService->revokeAdminAccessToken($userId);
                $this->forceSignIn->updateAdminSessionStatus($userId);
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
