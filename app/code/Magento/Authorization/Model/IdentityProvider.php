<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Authorization\Model;

use Magento\Framework\App\Backpressure\ContextInterface;
use Magento\Framework\App\Backpressure\IdentityProviderInterface;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;

/**
 * Utilizes UserContext for backpressure identity
 */
class IdentityProvider implements IdentityProviderInterface
{
    /**
     * User context identity type map
     */
    private const USER_CONTEXT_IDENTITY_TYPE_MAP = [
        UserContextInterface::USER_TYPE_CUSTOMER => ContextInterface::IDENTITY_TYPE_CUSTOMER,
        UserContextInterface::USER_TYPE_ADMIN => ContextInterface::IDENTITY_TYPE_ADMIN
    ];

    /**
     * @var UserContextInterface
     */
    private UserContextInterface $userContext;

    /**
     * @var RemoteAddress
     */
    private RemoteAddress $remoteAddress;

    /**
     * @param UserContextInterface $userContext
     * @param RemoteAddress $remoteAddress
     */
    public function __construct(UserContextInterface $userContext, RemoteAddress $remoteAddress)
    {
        $this->userContext = $userContext;
        $this->remoteAddress = $remoteAddress;
    }

    /**
     * @inheritDoc
     *
     * @throws RuntimeException
     */
    public function fetchIdentityType(): int
    {
        if (!$this->userContext->getUserId()) {
            return ContextInterface::IDENTITY_TYPE_IP;
        }

        $userType = $this->userContext->getUserType();
        if (isset(self::USER_CONTEXT_IDENTITY_TYPE_MAP[$userType])) {
            return self::USER_CONTEXT_IDENTITY_TYPE_MAP[$userType];
        }

        throw new RuntimeException(__('User type not defined'));
    }

    /**
     * @inheritDoc
     *
     * @throws RuntimeException
     */
    public function fetchIdentity(): string
    {
        $userId = $this->userContext->getUserId();
        if ($userId) {
            return (string)$userId;
        }

        $address = $this->remoteAddress->getRemoteAddress();
        if (!$address) {
            throw new RuntimeException(__('Failed to extract remote address'));
        }

        return $address;
    }
}
