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
        $select = $this->buildSelect(true, $entityIds);
        $query = $select->insertFromSelect($this->getMainTable());
        $this->getConnection()->query($query);

        $select = $this->buildSelect(false, $entityIds);
        $query = $select->insertFromSelect($this->getMainTable());
        $this->getConnection()->query($query);
    }

    private function buildSelect(bool $isAllGroups, array $entityIds = []): Select
    {
        $entityMetadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = $this->getConnection()->select();
        $select->from(['tp' => $this->tierPriceResourceModel->getMainTable()], [])
            ->where('tp.qty = ?', 1)
            ->where('tp.all_groups = ?', (int) $isAllGroups);
        if ($isAllGroups) {
            $select->where('tp.customer_group_id = ?', 0);
        }

        $select->join(['cpe' => $this->getTable('catalog_product_entity')], "cpe.{$linkField} = tp.{$linkField}", []);
        if (!empty($entityIds)) {
            $select->where('cpe.entity_id IN (?)', $entityIds);
        }

        $select->joinLeft(['sw' => $this->getTable('store_website')], 'sw.website_id = tp.website_id', []);
        $select->joinLeft(['sg' => $this->getTable('store_group')], 'sg.group_id = sw.default_group_id', []);

        $priceAttribute = $this->attributeRepository->get('price');
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

        $priceValue = $this->getConnection()->getIfNullSql('eps.value', 'ep0.value');
        $tierPriceValueExpr = $this->getConnection()->getCheckSql(
            'tp.value > 0',
            'tp.value',
            sprintf('(1 - %s / 100) * %s', 'tp.percentage_value', $priceValue)
        );
        $select->columns(
            [
                'cpe.entity_id',
                'tp.customer_group_id',
                'sw.website_id',
                'tier_price' => $tierPriceValueExpr,
            ]
        );

        return $select;
    }
}
