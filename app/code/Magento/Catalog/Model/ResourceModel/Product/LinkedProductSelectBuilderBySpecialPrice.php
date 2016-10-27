<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Select;
use Magento\Store\Model\Store;

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
     * @param BaseSelectProcessorInterface $baseSelectProcessor
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Helper\Data $catalogHelper,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        BaseSelectProcessorInterface $baseSelectProcessor = null
    ) {
        $this->storeManager = $storeManager;
        $this->resource = $resourceConnection;
        $this->eavConfig = $eavConfig;
        $this->catalogHelper = $catalogHelper;
        $this->dateTime = $dateTime;
        $this->localeDate = $localeDate;
        $this->baseSelectProcessor = (null !== $baseSelectProcessor)
            ? $baseSelectProcessor : ObjectManager::getInstance()->get(BaseSelectProcessorInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    public function build($productId)
    {
        $connection = $this->resource->getConnection();
        $specialPriceAttribute = $this->eavConfig->getAttribute(Product::ENTITY, 'special_price');
        $specialPriceFromDate = $this->eavConfig->getAttribute(Product::ENTITY, 'special_from_date');
        $specialPriceToDate = $this->eavConfig->getAttribute(Product::ENTITY, 'special_to_date');
        $timestamp = $this->localeDate->scopeTimeStamp($this->storeManager->getStore());
        $currentDate = $this->dateTime->formatDate($timestamp, false);

        $specialPrice = $connection->select()
            ->from(['t' => $specialPriceAttribute->getBackendTable()], 'entity_id')
            ->joinInner(
                [
                    BaseSelectProcessorInterface::PRODUCT_RELATION_ALIAS
                        => $this->resource->getTableName('catalog_product_relation')
                ],
                BaseSelectProcessorInterface::PRODUCT_RELATION_ALIAS . '.child_id = t.entity_id',
                []
            )->joinInner(
                ['special_from' => $specialPriceFromDate->getBackendTable()],
                $connection->quoteInto(
                    't.entity_id = special_from.entity_id AND special_from.attribute_id = ?',
                    $specialPriceFromDate->getAttributeId()
                ),
                ''
            )->joinInner(
                ['special_to' => $specialPriceToDate->getBackendTable()],
                $connection->quoteInto(
                    't.entity_id = special_to.entity_id AND special_to.attribute_id = ?',
                    $specialPriceToDate->getAttributeId()
                ),
                ''
            )->where(BaseSelectProcessorInterface::PRODUCT_RELATION_ALIAS . '.parent_id = ? ', $productId)
            ->where('t.attribute_id = ?', $specialPriceAttribute->getAttributeId())
            ->where('t.value IS NOT NULL')
            ->where(
                'special_from.value IS NULL OR ' . $connection->getDatePartSql('special_from.value') .' <= ?',
                $currentDate
            )->where(
                'special_to.value IS NULL OR ' . $connection->getDatePartSql('special_to.value') .' >= ?',
                $currentDate
            )->order('t.value ' . Select::SQL_ASC)
            ->limit(1);
        $specialPrice = $this->baseSelectProcessor->process($specialPrice);

        $specialPriceDefault = clone $specialPrice;
        $specialPriceDefault->where('t.store_id = ?', Store::DEFAULT_STORE_ID);
        $select[] = $specialPriceDefault;

        if (!$this->catalogHelper->isPriceGlobal()) {
            $specialPrice->where('t.store_id = ?', $this->storeManager->getStore()->getId());
            $select[] = $specialPrice;
        }

        return $select;
    }
}