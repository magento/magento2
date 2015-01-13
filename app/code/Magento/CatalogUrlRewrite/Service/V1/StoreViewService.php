<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Service\V1;

use Magento\Eav\Model\Config;
use Magento\Framework\App\Resource;

/**
 * Store view service
 */
class StoreViewService
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @param Config $eavConfig
     * @param \Magento\Framework\App\Resource $resource
     */
    public function __construct(
        Config $eavConfig,
        Resource $resource
    ) {
        $this->eavConfig = $eavConfig;
        $this->connection = $resource->getConnection(Resource::DEFAULT_READ_RESOURCE);
    }

    /**
     * Check that entity has overridden url key for specific store
     *
     * @param int $storeId
     * @param int $entityId
     * @param string $entityType
     * @throws \InvalidArgumentException
     * @return bool
     */
    public function doesEntityHaveOverriddenUrlKeyForStore($storeId, $entityId, $entityType)
    {
        $attribute = $this->eavConfig->getAttribute($entityType, 'url_key');
        if (!$attribute) {
            throw new \InvalidArgumentException(sprintf('Cannot retrieve attribute for entity type "%s"', $entityType));
        }
        $select = $this->connection->select()
        ->from($attribute->getBackendTable(), 'store_id')
        ->where('attribute_id = ?', $attribute->getId())
        ->where('entity_id = ?', $entityId);

        return in_array($storeId, $this->connection->fetchCol($select));
    }
}
