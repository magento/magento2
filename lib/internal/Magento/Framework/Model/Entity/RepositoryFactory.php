<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Entity;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Exception\NotFoundException;

/**
 * Class RepositoryFactory
 * @since 2.1.0
 */
class RepositoryFactory
{
    /**
     * List of entity types and their repositories
     *
     * @var array
     * @since 2.1.0
     */
    protected $entities;

    /**
     * Object Manager
     *
     * @var ObjectManagerInterface
     * @since 2.1.0
     */
    protected $objectManager;

    /**
     * RepositoryFactory constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param array $entities
     * @since 2.1.0
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $entities = []
    ) {
        $this->objectManager = $objectManager;
        $this->entities = $entities;
    }

    /**
     * @param string $entityType
     * @return object
     * @throws NotFoundException
     * @since 2.1.0
     */
    public function create($entityType)
    {
        if (!isset($this->entities[$entityType])) {
            $message = sprintf('Repository for entity type %s is not declared', $entityType);
            throw new NotFoundException(new \Magento\Framework\Phrase($message));
        }
        return $this->objectManager->get($this->entities[$entityType]);
    }
}
