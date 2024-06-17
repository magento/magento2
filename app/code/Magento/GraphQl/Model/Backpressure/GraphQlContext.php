<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\GraphQl\Model\Backpressure;

use Magento\Framework\App\Backpressure\ContextInterface;
use Magento\Framework\App\RequestInterface;

/**
 * GraphQl request context
 */
class GraphQlContext implements ContextInterface
{
    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var string
     */
    private string $identity;

    /**
     * @var int
     */
    private int $identityType;

    /**
     * @var string
     */
    private string $typeId;

    /**
     * @var string
     */
    private string $resolverClass;

    /**
     * @param RequestInterface $request
     * @param string $identity
     * @param int $identityType
     * @param string $typeId
     * @param string $resolverClass
     */
    public function __construct(
        RequestInterface $request,
        string $identity,
        int $identityType,
        string $typeId,
        string $resolverClass
    ) {
        $this->request = $request;
        $this->identity = $identity;
        $this->identityType = $identityType;
        $this->typeId = $typeId;
        $this->resolverClass = $resolverClass;
    }

    /**
     * @inheritDoc
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * @inheritDoc
     */
    public function getIdentity(): string
    {
        return $this->identity;
    }

    /**
     * @inheritDoc
     */
    public function getIdentityType(): int
    {
        return $this->identityType;
    }

    /**
     * @inheritDoc
     */
    public function getTypeId(): string
    {
        return $this->typeId;
    }

    /**
     * Field's resolver class name
     *
     * @return string
     */
    public function getResolverClass(): string
    {
        return $this->resolverClass;
    }
}
