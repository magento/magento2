<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Select;
use Magento\Store\Model\Store;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LinkedProductSelectBuilderBySpecialPrice implements LinkedProductSelectBuilderInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    private $catalogHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    private $dateTime;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * @var BaseSelectProcessorInterface
     */
    private $baseSelectProcessor;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Catalog\Helper\Data $catalogHelper
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param BaseSelectProcessorInterface $baseSelectProcessor
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Helper\Data $catalogHelper,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        BaseSelectProcessorInterface $baseSelectProcessor = null
    ) {
        $this->storeManager = $storeManager;
        $this->resource = $resourceConnection;
        $this->eavConfig = $eavConfig;
        $this->catalogHelper = $catalogHelper;
        $this->dateTime = $dateTime;
        $this->localeDate = $localeDate;
        $this->metadataPool = $metadataPool;
        $this->baseSelectProcessor = (null !== $baseSelectProcessor)
            ? $baseSelectProcessor : ObjectManager::getInstance()->get(BaseSelectProcessorInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    public function build($productId)
    {
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $connection = $this->resource->getConnection();
        $specialPriceAttribute = $this->eavConfig->getAttribute(Product::ENTITY, 'special_price');
        $specialPriceFromDate = $this->eavConfig->getAttribute(Product::ENTITY, 'special_from_date');
        $specialPriceToDate = $this->eavConfig->getAttribute(Product::ENTITY, 'special_to_date');
        $timestamp = $this->localeDate->scopeTimeStamp($this->storeManager->getStore());
        $currentDate = $this->dateTime->formatDate($timestamp, false);
        $productTable = $this->resource->getTableName('catalog_product_entity');

        $specialPrice = $this->resource->getConnection()->select()
            ->from(['parent' => $productTable], '')
            ->joinInner(
                ['link' => $this->resource->getTableName('catalog_product_relation')],
                "link.parent_id = parent.$linkField",
                []
            )->joinInner(
                [BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS => $productTable],
                sprintf('%s.entity_id = link.child_id', BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS),
                ['entity_id']
            )->joinInner(
                ['t' => $specialPriceAttribute->getBackendTable()],
                sprintf('t.%s = %s.%1$s', $linkField, BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS),
                []
            )->joinLeft(
                ['special_from' => $specialPriceFromDate->getBackendTable()],
                $connection->quoteInto(
                    "t.{$linkField} = special_from.{$linkField} AND special_from.attribute_id = ?",
                    $specialPriceFromDate->getAttributeId()
                ),
                ''
            )->joinLeft(
                ['special_to' => $specialPriceToDate->getBackendTable()],
                $connection->quoteInto(
                    "t.{$linkField} = special_to.{$linkField} AND special_to.attribute_id = ?",
                    $specialPriceToDate->getAttributeId()
                ),
                ''
            )->where('parent.entity_id = ?', $productId)
            ->where('t.attribute_id = ?', $specialPriceAttribute->getAttributeId())
            ->where('t.value IS NOT NULL')
            ->where(
                'special_from.value IS NULL OR ' . $connection->getDatePartSql('special_from.value') .' <= ?',
                $currentDate
            )->where(
                'special_to.value IS NULL OR ' . $connection->getDatePartSql('special_to.value') .' >= ?',
                $currentDate
            )->order('t.value ' . Select::SQL_ASC)
            ->order(BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS . '.' . $linkField . ' ' . Select::SQL_ASC)
            ->limit(1);
        $specialPrice = $this->baseSelectProcessor->process($specialPrice);

        if (!$this->catalogHelper->isPriceGlobal()) {
            $priceSelectStore = clone $specialPrice;
            $priceSelectStore->where('t.store_id = ?', $this->storeManager->getStore()->getId());
            $selects[] = $priceSelectStore;
        }

        $specialPrice->where('t.store_id = ?', Store::DEFAULT_STORE_ID);
        $selects[] = $specialPrice;

        return $selects;
    }
}
