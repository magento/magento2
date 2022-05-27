<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Model\Authorization;

use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\Webapi\Request;

/**
 * A user context determined by Adobe IMS tokens in a HTTP request Authorization header.
 */
class AdobeImsTokenUserContext implements UserContextInterface
{
    private const AUTHORIZATION_METHOD_HEADER_BEARER = 'bearer';

    /**
     * @var int|null
     */
    private ?int $userId = null;

    /**
     * @var bool
     */
    private bool $isRequestProcessed = false;

    /**
     * @var Request
     */
    private Request $request;

    /**
     * @var ImsConfig
     */
    private ImsConfig $adminImsConfig;

    /**
     * @var AdobeImsTokenUserService
     */
    private AdobeImsTokenUserService $tokenUserService;

    /**
     * @param Request $request
     * @param ImsConfig $adminImsConfig
     * @param AdobeImsTokenUserService $tokenUserService
     */
    public function __construct(
        Request $request,
        ImsConfig $adminImsConfig,
        AdobeImsTokenUserService $tokenUserService
    ) {
        $this->request = $request;
        $this->adminImsConfig = $adminImsConfig;
        $this->tokenUserService = $tokenUserService;
    }

    /**
     * @inheritdoc
     */
    public function getUserId(): ?int
    {
        $this->processRequest();
        return $this->userId;
    }

    /**
     * @inheritdoc
     */
    public function getUserType(): ?int
    {
        return UserContextInterface::USER_TYPE_ADMIN;
    }

    /**
     * Finds the bearer token and looks up the value.
     *
     * @return void
     * @throws AuthorizationException
     * @throws CouldNotSaveException
     * @throws InvalidArgumentException
     */
    private function processRequest()
    {
        if (!$this->adminImsConfig->enabled() || $this->isRequestProcessed) {
            return;
        }

        if (!$bearerToken = $this->getRequestedToken()) {
            return;
        }

        try {
            $adminUserId = $this->tokenUserService->getAdminUserIdByToken($bearerToken);
        } catch (AuthenticationException $e) {
            $this->isRequestProcessed = true;
            return;
        }

        $this->userId = $adminUserId;
        $this->isRequestProcessed = true;
    }

    /**
     * Getting requested token
     *
     * @return false|string
     */
    private function getRequestedToken()
    {
        $authorizationHeaderValue = $this->request->getHeader('Authorization');
        if (!$authorizationHeaderValue) {
            $this->isRequestProcessed = true;
            return false;
        }

        $headerPieces = explode(" ", $authorizationHeaderValue);
        if (count($headerPieces) !== 2) {
            $this->isRequestProcessed = true;
            return false;
        }

        $tokenType = strtolower($headerPieces[0]);
        if ($tokenType !== self::AUTHORIZATION_METHOD_HEADER_BEARER) {
            $this->isRequestProcessed = true;
            return false;
        }

        return $headerPieces[1];
    }
}
