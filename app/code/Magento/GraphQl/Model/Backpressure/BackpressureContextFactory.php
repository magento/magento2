<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\GraphQl\Model\Backpressure;

use Magento\Framework\App\Backpressure\ContextInterface;
use Magento\Framework\App\Backpressure\IdentityProviderInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\GraphQl\Config\Element\Field;

/**
 * Creates context for fields
 */
class BackpressureContextFactory
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
     * Creates context if possible
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

        return new GraphQlContext(
            $this->request,
            $this->identityProvider->fetchIdentity(),
            $this->identityProvider->fetchIdentityType(),
            $typeId,
            $field->getResolver()
        );
    }
}
