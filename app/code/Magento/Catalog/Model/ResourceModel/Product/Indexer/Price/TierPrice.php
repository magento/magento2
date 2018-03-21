<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Product\Indexer\Price;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice as TierPriceResourceModel;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\DB\Select;

/**
 * Class for filling tier price index table.
 */
class TierPrice extends AbstractDb
{
    /**
     * @var TierPriceResourceModel
     */
    private $tierPriceResourceModel;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @param Context $context
     * @param TierPriceResourceModel $tierPriceResourceModel
     * @param MetadataPool $metadataPool
     * @param ProductAttributeRepositoryInterface $attributeRepository
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        TierPriceResourceModel $tierPriceResourceModel,
        MetadataPool $metadataPool,
        ProductAttributeRepositoryInterface $attributeRepository,
        string $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        
        $this->tierPriceResourceModel = $tierPriceResourceModel;
        $this->metadataPool = $metadataPool;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('catalog_product_index_tier_price', 'entity_id');
    }

    /**
     * Reindex tier price for entities.
     *
     * @param array $entityIds
     * @return void
     */
    public function reindexEntity(array $entityIds = [])
    {
        $connection = $this->getConnection();
        $connection->delete($this->getMainTable());
        if (!$entityIds) {
            return;
        }

        $entityMetadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $entityMetadata->getLinkField();
        $entityField = 'cpe.' . $linkField;

        $priceAttribute = $this->attributeRepository->get('price');

        $select = $connection->select();
        $select->from(['sw' => $this->getTable('store_website')], [])
            ->joinCross(['cg' => $this->getTable('customer_group')], [])
            ->joinCross(['cpe' => $this->getTable('catalog_product_entity')], [])
            ->joinLeft(['sg' => $this->getTable('store_group')], 'sg.group_id = sw.default_group_id', [])
            ->where('cpe.entity_id IN (?)', $entityIds)
            ->having('tier_price IS NOT NULL');

        $select->joinLeft(
            ['eps' => $priceAttribute->getBackend()->getTable()],
            'eps.' . $linkField . ' = cpe.' . $linkField
            . ' AND eps.attribute_id = '.$priceAttribute->getAttributeId()
            . ' AND eps.store_id = sg.default_store_id',
            []
        )->joinLeft(
            ['ep0' => $priceAttribute->getBackend()->getTable()],
            'ep0.' . $linkField . ' = cpe.' . $linkField
            . ' AND ep0.attribute_id = '.$priceAttribute->getAttributeId()
            . ' AND ep0.store_id = 0',
            []
        );

        $tierPriceAliases = [];
        $tierPriceAliases[] = $this->joinTierPrice($select, $entityField, 'sw.website_id', 'cg.customer_group_id');
        $tierPriceAliases[] = $this->joinTierPrice($select, $entityField, 'sw.website_id', '0');
        $tierPriceAliases[] = $this->joinTierPrice($select, $entityField, '0', 'cg.customer_group_id');
        $tierPriceAliases[] = $this->joinTierPrice($select, $entityField, '0', '0');

        $tierPriceValueFields = $tierPricePercentageFields = [];
        foreach ($tierPriceAliases as $tierPriceAlias) {
            $tierPriceValueFields[] = $tierPriceAlias . '.value';
            $tierPricePercentageFields[] = $tierPriceAlias . '.percentage_value';
        }
        $tierPriceValue = 'COALESCE(' . implode(', ', $tierPriceValueFields) . ')';
        $tierPricePercentage = 'COALESCE(' . implode(', ', $tierPricePercentageFields) . ')';
        $priceValue = $connection->getIfNullSql('eps.value', 'ep0.value');
        $tierPriceValueExpr = $connection->getCheckSql(
            $tierPriceValue,
            $tierPriceValue,
            sprintf('(1 - %s / 100) * %s', $tierPricePercentage, $priceValue)
        );
        $select->columns(
            [
                'cpe.entity_id',
                'cg.customer_group_id',
                'sw.website_id',
                'tier_price' => $tierPriceValueExpr,
            ]
        );

        $query = $select->insertFromSelect($this->getMainTable());
        $connection->query($query);
    }

    /**
     * Join tier price table to select.
     *
     * @param Select $select
     * @param string $entityField
     * @param string $websiteField
     * @param string $customerGroupField
     * @return string
     */
    private function joinTierPrice(
        Select $select,
        string $entityField,
        string $websiteField,
        string $customerGroupField
    ): string {
        list (, $linkField) = explode('.', $entityField);
        $isAllGroups = ('0' === $customerGroupField ? '1' : '0');
        $tableAlias = 'tp' . ('0' === $websiteField ? '0' : 'w') . $isAllGroups;

        $select->joinLeft(
            [$tableAlias => $this->tierPriceResourceModel->getMainTable()],
            $tableAlias . '.' . $linkField . ' = ' . $entityField
            . ' AND ' . $tableAlias . '.qty = 1'
            . ' AND ' . $tableAlias . '.all_groups = ' . $isAllGroups
            . ' AND ' . $tableAlias . '.customer_group_id = ' . $customerGroupField
            . ' AND ' . $tableAlias . '.website_id = ' . $websiteField,
            []
        );

        return $tableAlias;
    }
}
