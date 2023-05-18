<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Resolver\Result;

/**
 * Composite hydrator for resolver result data.
 */
class HydratorComposite implements HydratorInterface
{
    /**
     * @var HydratorInterface[]
     */
    private array $hydrators = [];

    /**
     * @param HydratorInterface[] $hydrators
     */
    public function __construct(array $hydrators = [])
    {
        $this->hydrators = $hydrators;
    }

    /**
     * @inheritdoc
     */
    public function hydrate(array &$resolverData): void
    {
        if (empty($resolverData)) {
            return;
        }
        foreach ($this->hydrators as $hydrator) {
            $hydrator->hydrate($resolverData);
        }
    }
}
