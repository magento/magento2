<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\CatalogUrlRewrite\Model\ResourceModel\Product;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\AttributeInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\Store;

class IsUniqueUrlKey
{
    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ResourceConnection $resourceConnection
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->connection = $this->resourceConnection->getConnection();
        $this->eavConfig = $eavConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @param  string $urlKeyValue
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(string $urlKeyValue): bool
    {
        /** @var AttributeInterface $urlKeyAttribute */
        $urlKeyAttribute = $this->eavConfig->getAttribute(Product::ENTITY, ProductAttributeInterface::CODE_SEO_FIELD_URL_KEY);
        $urlKeyAttributeId = (int)$urlKeyAttribute->getId();

        $storeId = (int)$this->storeManager->getStore()->getId();
        $qry = $this->connection
            ->select()
            ->from(
                $this->resourceConnection->getTableName('catalog_product_entity_varchar')
            )->where(
                "attribute_id = $urlKeyAttributeId"
            )->where(
                "value = '$urlKeyValue'"
            )->where(
                "store_id = $storeId"
            );
        $result = $this->connection->fetchOne($qry);
        if (is_bool($result)) {
            return $this->checkUrlKeyExistDefaultStore($urlKeyAttributeId, $urlKeyValue);
        }
        return (bool)$result;
    }

    /**
     * @param int $urlKeyAttributeId
     * @param string $urlKeyValue
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function checkUrlKeyExistDefaultStore(int $urlKeyAttributeId, string $urlKeyValue): bool
    {
        $storeId = (int)$this->storeManager->getStore(Store::ADMIN_CODE)->getId();
        $qry = $this->connection
            ->select()
            ->from(
                $this->resourceConnection->getTableName('catalog_product_entity_varchar')
            )->where(
                "attribute_id = $urlKeyAttributeId"
            )->where(
                "value = '$urlKeyValue'"
            )->where(
                "store_id = $storeId"
            );
        return (bool)$this->connection->fetchOne($qry);
    }
}
