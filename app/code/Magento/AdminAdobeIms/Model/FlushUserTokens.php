<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Model;

use Exception;
use Magento\AdminAdobeIms\Api\ImsWebapiRepositoryInterface;
use Magento\AdobeImsApi\Api\FlushUserTokensInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class FlushUserTokens implements FlushUserTokensInterface
{
    /**
     * @var ImsWebapiRepositoryInterface
     */
    private ImsWebapiRepositoryInterface $imsWebapiRepository;

    /**
     * @var UserContextInterface
     */
    private UserContextInterface $userContext;

    /**
     * @var LogOut
     */
    private LogOut $logOut;

    /**
     * @var Encryptor
     */
    private Encryptor $encryptor;

    /**
     * FlushUserTokens constructor.
     *
     * @param ImsWebapiRepositoryInterface $imsWebapiRepository
     * @param UserContextInterface $userContext
     * @param LogOut $logOut
     * @param Encryptor $encryptor
     */
    public function __construct(
        ImsWebapiRepositoryInterface $imsWebapiRepository,
        UserContextInterface $userContext,
        LogOut $logOut,
        Encryptor $encryptor
    ) {
        $this->imsWebapiRepository = $imsWebapiRepository;
        $this->userContext = $userContext;
        $this->logOut = $logOut;
        $this->encryptor = $encryptor;
    }

    /**
     * @inheritdoc
     */
    public function execute(int $adminUserId = null): void
    {
        try {
            $adminUserId = $adminUserId ?? (int) $this->userContext->getUserId();

            $this->revokeTokenForAdobeIms($adminUserId);
            $this->removeTokensFromTable($adminUserId);
        } catch (Exception $exception) { //phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
            // User profile and tokens are not present in the system
        }
    }

    /**
     * Revoke tokens for adobe
     *
     * Get list of all tokens for adminUserId and invalidate them on adobe side
     *
     * @param int|null $adminUserId
     * @return void
     * @throws NoSuchEntityException
     * @throws Exception
     */
    private function revokeTokenForAdobeIms(int $adminUserId = null): void
    {
        $list = $this->imsWebapiRepository->getByAdminUserId($adminUserId);
        foreach ($list as $entity) {
            if ($entity->getAccessToken() !== null) {
                $this->logOut->execute(
                    $this->encryptor->decrypt($entity->getAccessToken())
                );
            }
        }
    }

    /**
     * Remove tokens from webapi table
     *
     * @param int|null $adminUserId
     * @return void
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    private function removeTokensFromTable(int $adminUserId = null): void
    {
        $this->imsWebapiRepository->deleteByAdminUserId($adminUserId);
    }
}
