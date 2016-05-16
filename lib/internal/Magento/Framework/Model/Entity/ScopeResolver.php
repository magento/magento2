<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Entity;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Phrase;

/**
 * Class ScopeResolver
 */
class ScopeResolver
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * ScopeResolver constructor.
     * @param ObjectManagerInterface $objectManager
     * @param MetadataPool $metadataPool
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
