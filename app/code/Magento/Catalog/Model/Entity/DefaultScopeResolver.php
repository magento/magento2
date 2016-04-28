<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Entity;

use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Phrase;
use Magento\Framework\Model\Entity\ScopeResolverInterface;
use Magento\Catalog\Model\Entity\DefaultStoreScopeProvider;

/**
 * Class DefaultScopeResolver
 */
class DefaultScopeResolver implements ScopeResolverInterface
{
    /**
     * @var DefaultStoreScopeProvider
     */
    private $storeScopeProvider;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * ScopeResolver constructor.
     * @param DefaultStoreScopeProvider $storeScopeProvider
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        DefaultStoreScopeProvider $storeScopeProvider,
        MetadataPool $metadataPool
    ) {
        $this->storeScopeProvider = $storeScopeProvider;
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
        for ($count=0; $count < count($metadata->getEntityContext()); $count++) {
            $entityContext[] = $this->storeScopeProvider->getContext($entityType, $entityData);
        }
        return $entityContext;
    }
}
