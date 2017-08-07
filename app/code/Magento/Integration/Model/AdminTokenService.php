<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Model;

use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Model\CredentialsValidator;
use Magento\Integration\Model\Oauth\Token as Token;
use Magento\Integration\Model\Oauth\TokenFactory as TokenModelFactory;
use Magento\Integration\Model\ResourceModel\Oauth\Token\CollectionFactory as TokenCollectionFactory;
use Magento\User\Model\User as UserModel;
use Magento\Integration\Model\Oauth\Token\RequestThrottler;

/**
 * Class to handle token generation for Admins
 */
class AdminTokenService implements \Magento\Integration\Api\AdminTokenServiceInterface
{
    /**
     * Token Model
     *
     * @var TokenModelFactory
     */
    private $tokenModelFactory;

    /**
     * User Model
     *
     * @var UserModel
     */
    private $userModel;

    /**
     * @var \Magento\Integration\Model\CredentialsValidator
     */
    private $validatorHelper;

    /**
     * Token Collection Factory
     *
     * @var TokenCollectionFactory
     */
    private $tokenModelCollectionFactory;

    /**
     * @var RequestThrottler
     * @since 2.0.3
     */
    private $requestThrottler;

    /**
     * Initialize service
     *
     * @param TokenModelFactory $tokenModelFactory
     * @param UserModel $userModel
     * @param TokenCollectionFactory $tokenModelCollectionFactory
     * @param \Magento\Integration\Model\CredentialsValidator $validatorHelper
     */
    public function __construct(
        TokenModelFactory $tokenModelFactory,
        UserModel $userModel,
        TokenCollectionFactory $tokenModelCollectionFactory,
        CredentialsValidator $validatorHelper
    ) {
        $this->tokenModelFactory = $tokenModelFactory;
        $this->userModel = $userModel;
        $this->tokenModelCollectionFactory = $tokenModelCollectionFactory;
        $this->validatorHelper = $validatorHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function createAdminAccessToken($username, $password)
    {
        $this->validatorHelper->validate($username, $password);
        $this->getRequestThrottler()->throttle($username, RequestThrottler::USER_TYPE_ADMIN);
        $this->userModel->login($username, $password);
        if (!$this->userModel->getId()) {
            $this->getRequestThrottler()->logAuthenticationFailure($username, RequestThrottler::USER_TYPE_ADMIN);
            /*
             * This message is same as one thrown in \Magento\Backend\Model\Auth to keep the behavior consistent.
             * Constant cannot be created in Auth Model since it uses legacy translation that doesn't support it.
             * Need to make sure that this is refactored once exception handling is updated in Auth Model.
             */
            throw new AuthenticationException(
                __('You did not sign in correctly or your account is temporarily disabled.')
            );
        }
        $this->getRequestThrottler()->resetAuthenticationFailuresCount($username, RequestThrottler::USER_TYPE_ADMIN);
        return $this->tokenModelFactory->create()->createAdminToken($this->userModel->getId())->getToken();
    }

    /**
     * Revoke token by admin id.
     *
     * The function will delete the token from the oauth_token table.
     *
     * @param int $adminId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function revokeAdminAccessToken($adminId)
    {
        $tokenCollection = $this->tokenModelCollectionFactory->create()->addFilterByAdminId($adminId);
        if ($tokenCollection->getSize() == 0) {
            throw new LocalizedException(__('This user has no tokens.'));
        }
        try {
            foreach ($tokenCollection as $token) {
                $token->delete();
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__('The tokens could not be revoked.'));
        }
        return true;
    }

    /**
     * Get request throttler instance
     *
     * @return RequestThrottler
     * @deprecated 2.0.3
     * @since 2.0.3
     */
    private function getRequestThrottler()
    {
        if (!$this->requestThrottler instanceof RequestThrottler) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(RequestThrottler::class);
        }
        return $this->requestThrottler;
    }
}
