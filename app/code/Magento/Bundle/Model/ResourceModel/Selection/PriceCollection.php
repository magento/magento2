<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\ResourceModel\Selection;

use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Pricing\Price\CatalogRulePrice;

/**
 * Bundle Price Selections Collection
 * Prepare collection with all price types for bundle and specific option
 * @deprecated Will be eliminated after catalog rule price will be calculated for bundle product
 */
class PriceCollection extends Collection
{
    /**
     * @var \Magento\CatalogRule\Model\ResourceModel\Product\Collection
     */
    private $catalogRuleProcessor;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrl,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Magento\CatalogRule\Model\ResourceModel\Product\Collection $catalogRuleProcessor,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null
    ) {

        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $eavConfig, $resource, $eavEntityFactory, $resourceHelper, $universalFactory, $storeManager, $moduleManager, $catalogProductFlatState, $scopeConfig, $productOptionFactory, $catalogUrl, $localeDate, $customerSession, $dateTime, $groupManagement, $connection);
        $this->catalogRuleProcessor = $catalogRuleProcessor;
    }

    /**
     * @param Product $product
     * @param bool $searchMin
     * @param bool $useRegularPrice
     */
    public function addPriceFilter($product, $searchMin, $useRegularPrice = false)
    {
        if ($product->getPriceType() == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
            $this->addPriceData();
            if ($useRegularPrice) {
                $minimalPriceExpression = 'minimal_price';
            } else {
                $this->catalogRuleProcessor->addPriceData($this, 'selection.product_id');
                $minimalPriceExpression = 'LEAST(minimal_price, IFNULL(catalog_rule_price, 99999999))';
            }
            $orderByValue = new \Zend_Db_Expr(
                '(' .
                $minimalPriceExpression .
                ' * selection.selection_qty' .
                ')'
            );

        } else {
            $connection = $this->getConnection();
            $websiteId = $this->_storeManager->getStore()->getWebsiteId();
            $priceType = $connection->getIfNullSql(
                'price.selection_price_type',
                'selection.selection_price_type'
            );
            $priceValue = $connection->getIfNullSql(
                'price.selection_price_value',
                'selection.selection_price_value'
            );
            $this->getSelect()->joinLeft(
                ['price' => $this->getTable('catalog_product_bundle_selection_price')],
                'selection.selection_id = price.selection_id AND price.website_id = ' . (int)$websiteId,
                []
            );
            $price = $connection->getCheckSql(
                $priceType . ' = 1',
                (float) $product->getPrice() . ' * '. $priceValue . ' / 100',
                $priceValue
            );
            $orderByValue = new \Zend_Db_Expr('('. $price. ' * '. 'selection.selection_qty)');
        }
        $this->getSelect()->order($orderByValue . ($searchMin ? \Zend_Db_Select::SQL_ASC : \Zend_Db_Select::SQL_DESC));

        $this->getSelect()->limit(1);
    }
}
