<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentCatalog\Model\ResourceModel;

use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Framework\App\ResourceConnection;
use Magento\MediaContentApi\Model\GetEntityContentsInterface;
use Magento\MediaContentApi\Api\Data\ContentIdentityInterface;
use Magento\Eav\Model\Config;

/**
 * Get concatenated content from custom attributes for all store views
 */
class GetCustomAttributesContent implements GetEntityContentsInterface
{
    private const EDITOR_FIELD = 'text';
    private const TEXT_ATTRIBUTE_TABLE = 'catalog_product_entity_text';
    private const CONTENT_TYPE = 'catalog_product';
    
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Product
     */
    private $productResource;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var array
     */
    private $excludedFields;

    /**
     * @param Config $config
     * @param ResourceConnection $resourceConnection
     * @param Product $productResource
     * @param array $excludedFields
     */
    public function __construct(
        Config $config,
        ResourceConnection $resourceConnection,
        Product $productResource,
        array $excludedFields
    ) {
        $this->config = $config;
        $this->productResource = $productResource;
        $this->resourceConnection = $resourceConnection;
        $this->excludedFields = $excludedFields;
    }

    /**
     * Get product custom attributes content for all store views
     *
     * @param ContentIdentityInterface $contentIdentity
     */
    public function execute(ContentIdentityInterface $contentIdentity): array
    {
        $attributes = $this->config->getEntityAttributes(self::CONTENT_TYPE);
        $attributesId = [];

        foreach ($attributes as $attribute) {
            if ($attribute->getBackendType() === self::EDITOR_FIELD &&
                !in_array($attribute->getAttributeCode(), $this->excludedFields)) {
                $attributesId[] = $attribute->getAttributeId();
            }
        }

        if (!empty($attributesId)) {
            $connection = $this->resourceConnection->getConnection();

            $select = $connection->select()->from(
                ['abt' => self::TEXT_ATTRIBUTE_TABLE],
                'abt.value'
            )->where(
                $connection->quoteIdentifier('abt.attribute_id') . ' IN (?)',
                $attributesId
            )->where(
                $connection->quoteIdentifier('abt.' . $attribute->getEntityIdField()) . ' = ?',
                $contentIdentity->getEntityId()
            )->distinct(true);

            return $connection->fetchCol($select);
        }
        return [];
    }
}
