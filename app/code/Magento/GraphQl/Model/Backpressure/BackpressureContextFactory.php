<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\GraphQl\Model\Backpressure;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\App\Backpressure\ContextInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;

/**
 * Creates context for fields.
 */
class BackpressureContextFactory
{
    private RequestTypeExtractorInterface $extractor;

    private UserContextInterface $userContext;

    private RemoteAddress $remoteAddress;

    private RequestInterface $request;

    /**
     * @param RequestTypeExtractorInterface $extractor
     * @param UserContextInterface $userContext
     * @param RemoteAddress $remoteAddress
     * @param RequestInterface $request
     */
    public function __construct(
        RequestTypeExtractorInterface $extractor,
        UserContextInterface $userContext,
        RemoteAddress $remoteAddress,
        RequestInterface $request
    ) {
        $this->extractor = $extractor;
        $this->userContext = $userContext;
        $this->remoteAddress = $remoteAddress;
        $this->request = $request;
    }

    /**
     * Creates context if possible.
     *
     * @param Field $field
     * @return ContextInterface|null
     */
    public function create(Field $field): ?ContextInterface
    {
        $typeId = $this->extractor->extract($field);
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

        return new GraphQlContext(
            $this->request,
            $identity,
            $identityType,
            $typeId,
            $field->getResolver()
        );
    }
}
