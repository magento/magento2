<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\App\Cache\Tag\Strategy;

use Magento\Framework\App\Cache\Tag\StrategyInterface;

/**
 * Locate GraphQL resolver cache tag strategy using configuration
 */
class Locator
{
    /**
     * Strategies map
     *
     * @var array
     */
    private $customStrategies = [];

    /**
     * @param array $customStrategies
     */
    public function __construct(
        array $customStrategies = []
    ) {
        $this->customStrategies = $customStrategies;
    }

    /**
     * Return GraphQL Resolver Cache tag strategy for specified object
     *
     * @param object $object
     * @throws \InvalidArgumentException
     * @return StrategyInterface|null
     */
    public function getStrategy($object): ?StrategyInterface
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException('Provided argument is not an object');
        }

        $classHierarchy = array_merge(
            [get_class($object) => get_class($object)],
            class_parents($object),
            class_implements($object)
        );

        $result = array_intersect(array_keys($this->customStrategies), $classHierarchy);
        $key = array_shift($result) ?? '';
        return $this->customStrategies[$key] ?? null;
    }
}
