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
        if ($isAllWebsites) {
            $select->joinCross(['sw' => $this->getTable('store_website')], [])
                ->where('sw.website_id > ?', 0)
                ->where('tp.website_id = ?', 0);
        } else {
            $select->join(
                ['sw' => $this->getTable('store_website')],
                'sw.website_id = tp.website_id',
                []
            )->where('tp.website_id > 0');
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
        if ($isAllCustomerGroups) {
            $select->joinCross(['cg' => $this->getTable('customer_group')], [])
                ->where('tp.all_groups = ?', 1)
                ->where('tp.customer_group_id = ?', 0);
        } else {
            $select->join(
                ['cg' => $this->getTable('customer_group')],
                'cg.customer_group_id = tp.customer_group_id',
                []
            )->where('tp.all_groups = ?', 0);
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
            ['ep0' => $priceAttribute->getBackend()->getTable()],
            'ep0.' . $linkField . ' = cpe.' . $linkField
            . ' AND ep0.attribute_id = '.$priceAttribute->getAttributeId()
            . ' AND ep0.store_id = 0',
            []
        );
        $priceValue = 'ep0.value';

        if (!$priceAttribute->isScopeGlobal()) {
            $select->joinLeft(['sg' => $this->getTable('store_group')], 'sg.group_id = sw.default_group_id', [])
                ->joinLeft(
                    ['eps' => $priceAttribute->getBackend()->getTable()],
                    'eps.' . $linkField . ' = cpe.' . $linkField
                    . ' AND eps.attribute_id = '.$priceAttribute->getAttributeId()
                    . ' AND eps.store_id = sg.default_store_id',
                    []
                );
            $priceValue = $this->getConnection()->getIfNullSql('eps.value', 'ep0.value');
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
        $select->from(['tp' => $this->tierPriceResourceModel->getMainTable()], [])
            ->where('tp.qty = ?', 1);

        $select->join(
            ['cpe' => $this->getTable('catalog_product_entity')],
            "cpe.{$linkField} = tp.{$linkField}",
            []
        );
        if (!empty($entityIds)) {
            $select->where('cpe.entity_id IN (?)', $entityIds);
        }
        $this->joinWebsites($select, $isAllWebsites);
        $this->joinCustomerGroups($select, $isAllCustomerGroups);

        $priceValue = $this->joinPrice($select, $linkField);
        $tierPriceValue = 'tp.value';
        $tierPricePercentageValue = 'tp.percentage_value';
        $tierPriceValueExpr = $this->getConnection()->getCheckSql(
            $tierPriceValue,
            $tierPriceValue,
            sprintf('(1 - %s / 100) * %s', $tierPricePercentageValue, $priceValue)
        );
        $select->columns(
            [
                'cpe.entity_id',
                'cg.customer_group_id',
                'sw.website_id',
                'tier_price' => $tierPriceValueExpr,
            ]
        );

        return $select;
    }
}
