<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Message;

use Magento\Framework\ObjectManagerInterface;

/**
 * Message collection factory
 * @since 2.0.0
 */
class CollectionFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return Collection
     * @since 2.0.0
     */
    public function create(array $data = [])
    {
        return $this->objectManager->create(\Magento\Framework\Message\Collection::class, $data);
    }
}
