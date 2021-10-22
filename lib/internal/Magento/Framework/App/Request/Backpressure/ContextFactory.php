<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\App\Request\Backpressure;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Backpressure\ContextInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;

/**
 * Creates context for current request.
 */
class ContextFactory
{
    private RequestTypeExtractorInterface $extractor;

    private UserContextInterface $userContext;

    private RequestInterface $request;

    private RemoteAddress $remoteAddress;

    /**
     * @param RequestTypeExtractorInterface $extractor
     * @param UserContextInterface $userContext
     * @param RequestInterface $request
     * @param RemoteAddress $remoteAddress
     */
    public function __construct(
        RequestTypeExtractorInterface $extractor,
        UserContextInterface $userContext,
        RequestInterface $request,
        RemoteAddress $remoteAddress
    ) {
        $this->extractor = $extractor;
        $this->userContext = $userContext;
        $this->request = $request;
        $this->remoteAddress = $remoteAddress;
    }

    /**
     * Create context if possible.
     *
     * @param ActionInterface $action
     * @return ContextInterface|null
     */
    public function create(ActionInterface $action): ?ContextInterface
    {
        $typeId = $this->extractor->extract($this->request, $action);
        if ($typeId === null) {
            return null;
        }

        if ($this->userContext->getUserType() === UserContextInterface::USER_TYPE_CUSTOMER
            && $this->userContext->getUserId()
        ) {
            $identityType = ContextInterface::IDENTITY_TYPE_CUSTOMER;
            $identity = (string) $this->userContext->getUserId();
        } elseif ($this->userContext->getUserType() === UserContextInterface::USER_TYPE_ADMIN
            && $this->userContext->getUserId()
        ) {
            $identityType = ContextInterface::IDENTITY_TYPE_ADMIN;
            $identity = (string) $this->userContext->getUserId();
        } else {
            $identityType = ContextInterface::IDENTITY_TYPE_IP;
            $identity = (string) $this->remoteAddress->getRemoteAddress();
        }

        return new ControllerContext(
            $this->request,
            $identity,
            $identityType,
            $typeId,
            $action
        );
    }
}
