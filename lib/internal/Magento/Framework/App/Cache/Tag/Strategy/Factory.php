<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Cache\Tag\Strategy;

use Magento\Framework\App\Cache\Tag\StrategyInterface;

/**
 * Creates strategies using configuration
 * @since 2.1.3
 */
class Factory
{
    /**
     * Default strategy for objects which implement Identity interface
     *
     * @var StrategyInterface
     * @since 2.1.3
     */
    private $identifierStrategy;

    /**
     * Strategy for objects which don't implement Identity interface
     *
     * @var StrategyInterface
     * @since 2.1.3
     */
    private $dummyStrategy;

    /**
     * Strategies map
     *
     * @var array
     * @since 2.1.3
     */
    private $customStrategies = [];

    /**
     * Factory constructor.
     *
     * @param Identifier $identifierStrategy
     * @param Dummy $dummyStrategy
     * @param array $customStrategies
     * @since 2.1.3
     */
    public function __construct(
        \Magento\Framework\App\Cache\Tag\Strategy\Identifier $identifierStrategy,
        \Magento\Framework\App\Cache\Tag\Strategy\Dummy $dummyStrategy,
        $customStrategies = []
    ) {
        $this->customStrategies = $customStrategies;
        $this->identifierStrategy = $identifierStrategy;
        $this->dummyStrategy = $dummyStrategy;
    }

    /**
     * Return tag strategy for specified object
     *
     * @param object $object
     * @throws \InvalidArgumentException
     * @return \Magento\Framework\App\Cache\Tag\StrategyInterface
     * @since 2.1.3
     */
    public function getStrategy($object)
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
        if ($result) {
            return $this->customStrategies[array_shift($result)];
        }

        if ($object instanceof \Magento\Framework\DataObject\IdentityInterface) {
            return $this->identifierStrategy;
        }

        return $this->dummyStrategy;
    }
}
