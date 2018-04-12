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
        $this->getConnection()->delete($this->getMainTable(), ['entity_id IN (?)' => $entityIds]);

        //separate by variations for increase performance
        $tierPriceVariations = [
            [true, true], //all websites; all customer groups
            [true, false], //all websites; specific customer group
            [false, true], //specific website; all customer groups
            [false, false], //specific website; specific customer group
        ];
        foreach ($tierPriceVariations as $variation) {
            list ($isAllWebsites, $isAllCustomerGroups) = $variation;
            $select = $this->getTierPriceSelect($isAllWebsites, $isAllCustomerGroups, $entityIds);
            $query = $select->insertFromSelect($this->getMainTable());
            $this->getConnection()->query($query);
        }
    }

    /**
     * Join websites table.
     * If $isAllWebsites is true, for each website will be used default value for all websites,
     * otherwise per each website will be used their own values.
     *
     * @param Select $select
     * @param bool $isAllWebsites
     */
    private function joinWebsites(Select $select, bool $isAllWebsites)
    {
        $websiteTable = ['website' => $this->getTable('store_website')];
        if ($isAllWebsites) {
            $select->joinCross($websiteTable, [])
                ->where('website.website_id > ?', 0)
                ->where('tier_price.website_id = ?', 0);
        } else {
            $select->join($websiteTable, 'website.website_id = tier_price.website_id', [])
                ->where('tier_price.website_id > 0');
        }
    }

    /**
     * Join customer groups table.
     * If $isAllCustomerGroups is true, for each customer group will be used default value for all customer groups,
     * otherwise per each customer group will be used their own values.
     *
     * @param Select $select
     * @param bool $isAllCustomerGroups
     */
    private function joinCustomerGroups(Select $select, bool $isAllCustomerGroups)
    {
        $customerGroupTable = ['customer_group' => $this->getTable('customer_group')];
        if ($isAllCustomerGroups) {
            $select->joinCross($customerGroupTable, [])
                ->where('tier_price.all_groups = ?', 1)
                ->where('tier_price.customer_group_id = ?', 0);
        } else {
            $select->join($customerGroupTable, 'customer_group.customer_group_id = tier_price.customer_group_id', [])
                ->where('tier_price.all_groups = ?', 0);
        }
    }

    /**
     * Join price table and return price value.
     *
     * @param Select $select
     * @param string $linkField
     * @return string
     */
    private function joinPrice(Select $select, string $linkField): string
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $priceAttribute */
        $priceAttribute = $this->attributeRepository->get('price');
        $select->joinLeft(
            ['entity_price_default' => $priceAttribute->getBackend()->getTable()],
            'entity_price_default.' . $linkField . ' = entity.' . $linkField
            . ' AND entity_price_default.attribute_id = ' . $priceAttribute->getAttributeId()
            . ' AND entity_price_default.store_id = 0',
            []
        );
        $priceValue = 'entity_price_default.value';

        if (!$priceAttribute->isScopeGlobal()) {
            $select->joinLeft(
                ['store_group' => $this->getTable('store_group')],
                'store_group.group_id = website.default_group_id',
                []
            )->joinLeft(
                ['entity_price_store' => $priceAttribute->getBackend()->getTable()],
                'entity_price_store.' . $linkField . ' = entity.' . $linkField
                . ' AND entity_price_store.attribute_id = ' . $priceAttribute->getAttributeId()
                . ' AND entity_price_store.store_id = store_group.default_store_id',
                []
            );
            $priceValue = $this->getConnection()
                ->getIfNullSql('entity_price_store.value', 'entity_price_default.value');
        }

        return (string) $priceValue;
    }

    /**
     * Build select for getting tier price data.
     *
     * @param bool $isAllWebsites
     * @param bool $isAllCustomerGroups
     * @param array $entityIds
     * @return Select
     */
    private function getTierPriceSelect(bool $isAllWebsites, bool $isAllCustomerGroups, array $entityIds = []): Select
    {
        $entityMetadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = $this->getConnection()->select();
        $select->from(['tier_price' => $this->tierPriceResourceModel->getMainTable()], [])
            ->where('tier_price.qty = ?', 1);

        $select->join(
            ['entity' => $this->getTable('catalog_product_entity')],
            "entity.{$linkField} = tier_price.{$linkField}",
            []
        );
        if (!empty($entityIds)) {
            $select->where('entity.entity_id IN (?)', $entityIds);
        }
        $this->joinWebsites($select, $isAllWebsites);
        $this->joinCustomerGroups($select, $isAllCustomerGroups);

        $priceValue = $this->joinPrice($select, $linkField);
        $tierPriceValue = 'tier_price.value';
        $tierPricePercentageValue = 'tier_price.percentage_value';
        $tierPriceValueExpr = $this->getConnection()->getCheckSql(
            $tierPriceValue,
            $tierPriceValue,
            sprintf('(1 - %s / 100) * %s', $tierPricePercentageValue, $priceValue)
        );
        $select->columns(
            [
                'entity.entity_id',
                'customer_group.customer_group_id',
                'website.website_id',
                'tier_price' => $tierPriceValueExpr,
            ]
        );

        return $select;
    }
}
