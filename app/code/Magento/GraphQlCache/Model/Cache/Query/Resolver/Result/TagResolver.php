<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Model\Cache\Query\Resolver\Result;

use Magento\Framework\App\Cache\Tag\Resolver;
use Magento\Framework\App\Cache\Tag\Strategy\Factory as StrategyFactory;

class TagResolver extends Resolver
{
    /**
     * @var array
     */
    private $invalidatableObjectTypes;

    /**
     * GraphQL Resolver cache-specific tag resolver for the purpose of invalidation
     *
     * @param StrategyFactory $factory
     * @param array $invalidatableObjectTypes
     */
    public function __construct(
        StrategyFactory $factory,
        array $invalidatableObjectTypes = []
    ) {
        $this->invalidatableObjectTypes = $invalidatableObjectTypes;

        parent::__construct($factory);
    }

    /**
     * @inheritdoc
     */
    public function getTags($object)
    {
        $isInvalidatable = false;

        foreach ($this->invalidatableObjectTypes as $invalidatableObjectType) {
            $isInvalidatable = $object instanceof $invalidatableObjectType;

            if ($isInvalidatable) {
                break;
            }
        }

        if (!$isInvalidatable) {
            return [];
        }

        return parent::getTags($object);
    }
}
