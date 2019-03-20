<?php

declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Model\ResourceModel\Product;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\AttributeInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\App\ResourceConnection;

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
     * @param ResourceConnection $resourceConnection
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->connection = $this->resourceConnection->getConnection();
        $this->eavConfig = $eavConfig;
    }

    /**
     * @param  string $urlKeyValue
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(string $urlKeyValue)
    {
        /** @var AttributeInterface $urlKeyAttribute */
        $urlKeyAttribute = $this->eavConfig->getAttribute(Product::ENTITY, ProductAttributeInterface::CODE_SEO_FIELD_URL_KEY);
        $urlKeyAttributeId = (int)$urlKeyAttribute->getId();

        $qry = $this->connection
            ->select()
            ->from(
                $this->resourceConnection->getTableName('catalog_product_entity_varchar')
            )->where(
                "attribute_id = $urlKeyAttributeId"
            )->where(
                "value = '$urlKeyValue'"
            );

        return (bool)$this->connection->fetchOne($qry);
    }
}
