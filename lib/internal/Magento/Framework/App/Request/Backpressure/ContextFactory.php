<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\App\Request\Backpressure;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Backpressure\ContextInterface;
use Magento\Framework\App\Backpressure\IdentityProviderInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Creates context for current request
 */
class ContextFactory
{
    /**
     * @var RequestTypeExtractorInterface
     */
    private RequestTypeExtractorInterface $extractor;

    /**
     * @var IdentityProviderInterface
     */
    private IdentityProviderInterface $identityProvider;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @param RequestTypeExtractorInterface $extractor
     * @param IdentityProviderInterface $identityProvider
     * @param RequestInterface $request
     */
    public function __construct(
        RequestTypeExtractorInterface $extractor,
        IdentityProviderInterface $identityProvider,
        RequestInterface $request
    ) {
        $this->extractor = $extractor;
        $this->identityProvider = $identityProvider;
        $this->request = $request;
    }

    /**
     * Create context if possible
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

        return new ControllerContext(
            $this->request,
            $this->identityProvider->fetchIdentity(),
            $this->identityProvider->fetchIdentityType(),
            $typeId,
            $action
        );
    }
}
