<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Webapi\Backpressure;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\App\Backpressure\ContextInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;

/**
 * Creates backpressure context for a request.
 */
class BackpressureContextFactory
{
    private RequestInterface $request;

    private UserContextInterface $userContext;

    private RemoteAddress $remoteAddress;

    private BackpressureRequestTypeExtractorInterface $extractor;

    /**
     * @param RequestInterface $request
     * @param UserContextInterface $userContext
     * @param RemoteAddress $remoteAddress
     * @param BackpressureRequestTypeExtractorInterface $extractor
     */
    public function __construct(
        RequestInterface $request,
        UserContextInterface $userContext,
        RemoteAddress $remoteAddress,
        BackpressureRequestTypeExtractorInterface $extractor
    ) {
        $this->request = $request;
        $this->userContext = $userContext;
        $this->remoteAddress = $remoteAddress;
        $this->extractor = $extractor;
    }

    /**
     * Create context if possible for current request.
     *
     * @param string $service Service class
     * @param string $method Service method
     * @param string $endpoint Endpoint
     * @return ContextInterface|null
     */
    public function create(string $service, string $method, string $endpoint): ?ContextInterface
    {
        $typeId = $this->extractor->extract($service, $method, $endpoint);
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

        return new RestContext(
            $this->request,
            $identity,
            $identityType,
            $typeId,
            $service,
            $method,
            $endpoint
        );
    }
}
