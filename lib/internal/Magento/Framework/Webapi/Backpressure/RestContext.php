<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Webapi\Backpressure;

use Magento\Framework\App\Backpressure\ContextInterface;
use Magento\Framework\App\RequestInterface;

/**
 * REST request context
 */
class RestContext implements ContextInterface
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
    private string $service;

    /**
     * @var string
     */
    private string $method;

    /**
     * @var string
     */
    private string $endpoint;

    /**
     * @param RequestInterface $request
     * @param string $identity
     * @param int $identityType
     * @param string $typeId
     * @param string $service
     * @param string $method
     * @param string $endpoint
     */
    public function __construct(
        RequestInterface $request,
        string $identity,
        int $identityType,
        string $typeId,
        string $service,
        string $method,
        string $endpoint
    ) {
        $this->request = $request;
        $this->identity = $identity;
        $this->identityType = $identityType;
        $this->typeId = $typeId;
        $this->service = $service;
        $this->method = $method;
        $this->endpoint = $endpoint;
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
     * Service class name
     *
     * @return string
     */
    public function getService(): string
    {
        return $this->service;
    }

    /**
     * Service method
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Endpoint route
     *
     * @return string
     */
    public function getEndpoint(): string
    {
        return $this->endpoint;
    }
}
