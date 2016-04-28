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
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreScopeProvider;
use Magento\Store\Model\Store;

/**
 * Class DefaultScopeResolver
 */
class DefaultScopeResolver implements ScopeResolverInterface
{
    /**
     * @var StoreScopeProvider
     */
    private $storeScopeProvider;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * ScopeResolver constructor.
     * @param StoreScopeProvider $storeScopeProvider
     * @param StoreManagerInterface $storeManager
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        StoreScopeProvider $storeScopeProvider,
        StoreManagerInterface $storeManager,
        MetadataPool $metadataPool
    ) {
        $this->storeScopeProvider = $storeScopeProvider;
        $this->storeManager = $storeManager;
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
        $entityData = $this->resolveScopeForDefault($entityData);
        $entityContext = [];
        $metadata = $this->metadataPool->getMetadata($entityType);
        for ($count=0; $count < count($metadata->getEntityContext()); $count++) {
            $entityContext[] = $this->storeScopeProvider->getContext($entityType, $entityData);
        }
        return $entityContext;
    }

    /**
     * @param array $entityData
     * @return array
     */
    private function resolveScopeForDefault($entityData)
    {
        if ($entityData[Store::STORE_ID] == $this->storeManager->getDefaultStoreView()->getId()) {
            $entityData[Store::STORE_ID] = Store::DEFAULT_STORE_ID;
        }
        return $entityData;
    }
}
