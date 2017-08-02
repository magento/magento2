<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Cache\Tag;

/**
 * Resolves invalidation tags for specified object using different strategies
 * @since 2.2.0
 */
class Resolver
{
    /**
     * Tag strategies factory
     *
     * @var Strategy\Factory
     * @since 2.2.0
     */
    private $strategyFactory;

    /**
     * Resolver constructor.
     *
     * @param Strategy\Factory $factory
     * @since 2.2.0
     */
    public function __construct(\Magento\Framework\App\Cache\Tag\Strategy\Factory $factory)
    {
        $this->strategyFactory = $factory;
    }

    /**
     * Identify invalidation tags for the object using custom strategies
     *
     * @param object $object
     * @throws \InvalidArgumentException
     * @return array
     * @since 2.2.0
     */
    public function getTags($object)
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException('Provided argument is not an object');
        }

        return $this->strategyFactory->getStrategy($object)->getTags($object);
    }
}
