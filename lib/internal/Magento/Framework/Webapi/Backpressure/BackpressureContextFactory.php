<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Webapi\Backpressure;

use Magento\Framework\App\Backpressure\ContextInterface;
use Magento\Framework\App\Backpressure\IdentityProviderInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Creates backpressure context for a request
 */
class BackpressureContextFactory
{
    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var IdentityProviderInterface
     */
    private IdentityProviderInterface $identityProvider;

    /**
     * @var BackpressureRequestTypeExtractorInterface
     */
    private BackpressureRequestTypeExtractorInterface $extractor;

    /**
     * @param RequestInterface $request
     * @param IdentityProviderInterface $identityProvider
     * @param BackpressureRequestTypeExtractorInterface $extractor
     */
    public function __construct(
        RequestInterface $request,
        IdentityProviderInterface $identityProvider,
        BackpressureRequestTypeExtractorInterface $extractor
    ) {
        $this->request = $request;
        $this->identityProvider = $identityProvider;
        $this->extractor = $extractor;
    }

    /**
     * Create context if possible for current request
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

        return new RestContext(
            $this->request,
            $this->identityProvider->fetchIdentity(),
            $this->identityProvider->fetchIdentityType(),
            $typeId,
            $service,
            $method,
            $endpoint
        );
    }
}
