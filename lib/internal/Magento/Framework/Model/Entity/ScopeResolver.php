<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Entity;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Phrase;

/**
 * Class ScopeResolver
 * @since 2.1.0
 */
class ScopeResolver
{
    /**
     * @var ObjectManagerInterface
     * @since 2.1.0
     */
    private $objectManager;

    /**
     * @var MetadataPool
     * @since 2.1.0
     */
    private $metadataPool;

    /**
     * ScopeResolver constructor.
     * @param ObjectManagerInterface $objectManager
     * @param MetadataPool $metadataPool
     * @since 2.1.0
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        MetadataPool $metadataPool
    ) {
        $this->objectManager = $objectManager;
        $this->metadataPool = $metadataPool;
    }

    /**
     * @param string $entityType
     * @param array|null $entityData
     * @return \Magento\Framework\Model\Entity\ScopeInterface[]
     * @throws ConfigurationMismatchException
     * @throws \Exception
     * @since 2.1.0
     */
    public function getEntityContext($entityType, $entityData = [])
    {
        $entityContext = [];
        $metadata = $this->metadataPool->getMetadata($entityType);
        foreach ($metadata->getEntityContext() as $contextProviderClass) {
            $contextProvider =  $this->objectManager->get($contextProviderClass);
            if (!$contextProvider instanceof ScopeProviderInterface) {
                throw new ConfigurationMismatchException(new Phrase('Wrong configuration for type %1', [$entityType]));
            }
            $entityContext[] = $contextProvider->getContext($entityType, $entityData);
        }
        return $entityContext;
    }
}
