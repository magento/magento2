<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Authorization\Model;

use Magento\Framework\App\Backpressure\ContextInterface;
use Magento\Framework\App\Backpressure\IdentityProviderInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;

/**
 * Utilizes UserContext for backpressure identity.
 */
class IdentityProvider implements IdentityProviderInterface
{
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
     */
    public function fetchIdentityType(): int
    {
        if ($this->userContext->getUserType() === UserContextInterface::USER_TYPE_CUSTOMER
            && $this->userContext->getUserId()
        ) {
            return ContextInterface::IDENTITY_TYPE_CUSTOMER;
        } elseif ($this->userContext->getUserType() === UserContextInterface::USER_TYPE_ADMIN
            && $this->userContext->getUserId()
        ) {
            return ContextInterface::IDENTITY_TYPE_ADMIN;
        } else {
            return ContextInterface::IDENTITY_TYPE_IP;
        }
    }

    /**
     * @inheritDoc
     */
    public function fetchIdentity(): string
    {
        if ($this->userContext->getUserId()) {
            return (string) $this->userContext->getUserId();
        }

        $addr = $this->remoteAddress->getRemoteAddress();
        if (!$addr) {
            throw new \RuntimeException('Failed to extract remote address');
        }

        return $addr;
    }
}
