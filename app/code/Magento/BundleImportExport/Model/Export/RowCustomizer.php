<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\BundleImportExport\Model\Export;

use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\Selection;
use Magento\Catalog\Helper\Data as CatalogData;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;
use Magento\CatalogImportExport\Model\Import\Product as ImportProductModel;
use Magento\Bundle\Model\ResourceModel\Selection\Collection as SelectionCollection;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\ImportExport\Model\Import as ImportModel;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RowCustomizer implements RowCustomizerInterface
{
    public const BUNDLE_PRICE_TYPE_COL = 'bundle_price_type';

    public const BUNDLE_SKU_TYPE_COL = 'bundle_sku_type';

    public const BUNDLE_PRICE_VIEW_COL = 'bundle_price_view';

    public const BUNDLE_WEIGHT_TYPE_COL = 'bundle_weight_type';

    public const BUNDLE_VALUES_COL = 'bundle_values';

    public const VALUE_FIXED = 'fixed';

    public const VALUE_DYNAMIC = 'dynamic';

    public const VALUE_PERCENT = 'percent';

    public const VALUE_PRICE_RANGE = 'Price range';

    public const VALUE_AS_LOW_AS = 'As low as';

    /**
     * Mapping for bundle types
     *
     * @var array
     */
    protected $typeMapping = [
        '0' => self::VALUE_DYNAMIC,
        '1' => self::VALUE_FIXED
    ];

    /**
     * Mapping for price views
     *
     * @var array
     */
    protected $priceViewMapping = [
        '0' => self::VALUE_PRICE_RANGE,
        '1' => self::VALUE_AS_LOW_AS
    ];

    /**
     * Mapping for price types
     *
     * @var array
     */
    protected $priceTypeMapping = [
        '0' => self::VALUE_FIXED,
        '1' => self::VALUE_PERCENT
    ];

    /**
     * Bundle product columns
     *
     * @var array
     */
    protected $bundleColumns = [
        self::BUNDLE_PRICE_TYPE_COL,
        self::BUNDLE_SKU_TYPE_COL,
        self::BUNDLE_PRICE_VIEW_COL,
        self::BUNDLE_WEIGHT_TYPE_COL,
        self::BUNDLE_VALUES_COL
    ];

    /**
     * Product's bundle data
     *
     * @var array
     */
    protected $bundleData = [];

    /**
     * Column name for shipment_type attribute
     *
     * @var string
     */
    private $shipmentTypeColumn = 'bundle_shipment_type';

    /**
     * Mapping for shipment type
     *
     * @var array
     */
    private $shipmentTypeMapping = [
        AbstractType::SHIPMENT_TOGETHER => 'together',
        AbstractType::SHIPMENT_SEPARATELY => 'separately',
    ];

    /**
     * @var \Magento\Bundle\Model\ResourceModel\Option\Collection[]
     */
    private $optionCollections = [];

    /**
     * @var array
     */
    private $storeIdToCode = [];

    /**
     * @var string
     */
    private $optionCollectionCacheKey = '_cache_instance_options_collection';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CatalogData
     */
    private $catalogData;

    /**
     * @param StoreManagerInterface $storeManager
     * @param CatalogData|null $catalogData
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ?CatalogData $catalogData = null
    ) {
        $this->storeManager = $storeManager;
        $this->catalogData = $catalogData ?? ObjectManager::getInstance()->get(CatalogData::class);
    }

    /**
     * Retrieve list of bundle specific columns
     *
     * @return array
     */
    private function getBundleColumns()
    {
        return array_merge($this->bundleColumns, [$this->shipmentTypeColumn]);
    }

    /**
     * Prepare data for export
     *
     * @param Collection $collection
     * @param int[] $productIds
     * @return $this
     */
    public function prepareData($collection, $productIds)
    {
        $productCollection = clone $collection;
        $productCollection->addAttributeToFilter(
            'entity_id',
            ['in' => $productIds]
        )->addAttributeToFilter(
            'type_id',
            ['eq' => Type::TYPE_BUNDLE]
        );

        return $this->populateBundleData($productCollection);
    }

    /**
     * Set headers columns
     *
     * @param array $columns
     * @return array
     */
    public function addHeaderColumns($columns)
    {
        $columns = array_merge($columns, $this->getBundleColumns());

        return $columns;
    }

    /**
     * Add data for export
     *
     * @param array $dataRow
     * @param int $productId
     * @return array
     */
    public function addData($dataRow, $productId)
    {
        if (!empty($this->bundleData[$productId])) {
            $dataRow = array_merge($this->cleanNotBundleAdditionalAttributes($dataRow), $this->bundleData[$productId]);
        }

        return $dataRow;
    }

    /**
     * Calculate the largest links block
     *
     * @param array $additionalRowsCount
     * @param int $productId
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getAdditionalRowsCount($additionalRowsCount, $productId)
    {
        return $additionalRowsCount;
    }

    /**
     * Populate bundle product data
     *
     * @param Collection $collection
     * @return $this
     */
    protected function populateBundleData($collection)
    {
        foreach ($collection as $product) {
            $id = $product->getEntityId();
            $this->bundleData[$id][self::BUNDLE_PRICE_TYPE_COL] = $this->getTypeValue($product->getPriceType());
            $this->bundleData[$id][$this->shipmentTypeColumn] = $this->getShipmentTypeValue(
                $product->getShipmentType()
            );
            $this->bundleData[$id][self::BUNDLE_SKU_TYPE_COL] = $this->getTypeValue($product->getSkuType());
            $this->bundleData[$id][self::BUNDLE_PRICE_VIEW_COL] = $this->getPriceViewValue($product->getPriceView());
            $this->bundleData[$id][self::BUNDLE_WEIGHT_TYPE_COL] = $this->getTypeValue($product->getWeightType());
            $this->bundleData[$id][self::BUNDLE_VALUES_COL] = $this->getFormattedBundleOptionValues($product);
            // cleanup memory
            unset($this->optionCollections[$product->getSku()]);
        }
        return $this;
    }

    /**
     * Retrieve formatted bundle options
     *
     * @param Product $product
     * @return string
     */
    protected function getFormattedBundleOptionValues(Product $product): string
    {
        $optionCollections = $this->getProductOptionCollection($product);
        $bundleData = '';
        $optionTitles = $this->getBundleOptionTitles($product);
        $optionsRawSelections = $this->getBundleOptionSelections($product);
        foreach ($optionCollections->getItems() as $option) {
            $optionValues = $this->getFormattedOptionValues($option, $optionTitles);
            $bundleData .= implode(
                '',
                array_map(
                    fn ($selectionData) => $optionValues
                        . ImportModel::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR
                        . $this->serialize($selectionData)
                        . ImportProductModel::PSEUDO_MULTI_LINE_SEPARATOR,
                    $optionsRawSelections[$option->getOptionId()] ?? []
                )
            );
        }

        return rtrim($bundleData, ImportProductModel::PSEUDO_MULTI_LINE_SEPARATOR);
    }

    /**
     * Retrieve formatted bundle selections
     *
     * @param string $optionValues
     * @param SelectionCollection $selections
     * @return string
     * @deprecared Not used anymore
     */
    protected function getFormattedBundleSelections($optionValues, SelectionCollection $selections)
    {
        $bundleData = '';
        $selections->addAttributeToSort('position');
        foreach ($selections as $selection) {
            $selectionData = [
                'sku' => $selection->getSku(),
                'price' => $selection->getSelectionPriceValue(),
                'default' => $selection->getIsDefault(),
                'default_qty' => $selection->getSelectionQty(),
                'price_type' => $this->getPriceTypeValue($selection->getSelectionPriceType()),
                'can_change_qty' => $selection->getSelectionCanChangeQty(),
            ];
            $bundleData .= $optionValues
                . ImportModel::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR
                . $this->serialize($selectionData)
                . ImportProductModel::PSEUDO_MULTI_LINE_SEPARATOR;
        }

        return $bundleData;
    }

    /**
     * Retrieve option value of bundle product
     *
     * @param Option $option
     * @param string[] $optionTitles
     * @return string
     */
    protected function getFormattedOptionValues(
        Option $option,
        array $optionTitles = []
    ): string {
        $data = [
            ...[
                'name' => $option->getTitle()
            ],
            ...($optionTitles[$option->getOptionId()] ?? []),
            ...[
                'type' => $option->getType(),
                'required' => $option->getRequired()
            ]
        ];

        return $this->serialize($data);
    }

    /**
     * Format associative array to serialized string as name1=value1,name2=value2 format
     *
     * @param array $data
     * @return string
     */
    private function serialize(array $data): string
    {
        return implode(
            ImportModel::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR,
            array_map(
                function ($value, $key) {
                    return $key . ImportProductModel::PAIR_NAME_VALUE_SEPARATOR . $value;
                },
                $data,
                array_keys($data)
            )
        );
    }

    /**
     * Retrieve bundle type value by code
     *
     * @param string $type
     * @return string
     */
    protected function getTypeValue($type)
    {
        return $this->typeMapping[$type] ?? self::VALUE_DYNAMIC;
    }

    /**
     * Retrieve bundle price view value by code
     *
     * @param string $type
     * @return string
     */
    protected function getPriceViewValue($type)
    {
        return $this->priceViewMapping[$type] ?? self::VALUE_PRICE_RANGE;
    }

    /**
     * Retrieve bundle price type value by code
     *
     * @param string $type
     * @return string
     */
    protected function getPriceTypeValue($type)
    {
        return $this->priceTypeMapping[$type] ?? null;
    }

    /**
     * Retrieve bundle shipment type value by code
     *
     * @param string $type
     * @return string
     */
    private function getShipmentTypeValue($type)
    {
        return $this->shipmentTypeMapping[$type] ?? null;
    }

    /**
     * Remove bundle specified additional attributes as now they are stored in specified columns
     *
     * @param array $dataRow
     * @return array
     */
    protected function cleanNotBundleAdditionalAttributes($dataRow)
    {
        if (!empty($dataRow['additional_attributes'])) {
            $additionalAttributes = $this->parseAdditionalAttributes($dataRow['additional_attributes']);
            $dataRow['additional_attributes'] = $this->getNotBundleAttributes($additionalAttributes);
        }

        return $dataRow;
    }

    /**
     * Retrieve not bundle additional attributes
     *
     * @param array $additionalAttributes
     * @return string
     */
    protected function getNotBundleAttributes($additionalAttributes)
    {
        $filteredAttributes = [];
        foreach ($additionalAttributes as $code => $value) {
            if (!in_array('bundle_' . $code, $this->getBundleColumns())) {
                $filteredAttributes[] = $code . ImportProductModel::PAIR_NAME_VALUE_SEPARATOR . $value;
            }
        }
        return implode(ImportModel::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $filteredAttributes);
    }

    /**
     * Retrieves additional attributes as array code=>value.
     *
     * @param string $additionalAttributes
     * @return array
     */
    private function parseAdditionalAttributes($additionalAttributes)
    {
        $attributeNameValuePairs = explode(ImportModel::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $additionalAttributes);
        $preparedAttributes = [];
        $code = '';
        foreach ($attributeNameValuePairs as $attributeData) {
            //process case when attribute has ImportModel::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR inside its value
            if (strpos($attributeData, ImportProductModel::PAIR_NAME_VALUE_SEPARATOR) === false) {
                if (!$code) {
                    continue;
                }
                $preparedAttributes[$code] .= ImportModel::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR . $attributeData;
                continue;
            }
            list($code, $value) = explode(ImportProductModel::PAIR_NAME_VALUE_SEPARATOR, $attributeData, 2);
            $preparedAttributes[$code] = $value;
        }
        return $preparedAttributes;
    }

    /**
     * Get product options titles.
     *
     * Values for all store views (default) should be specified with 'name' key.
     * If user want to specify value or change existing for non default store views it should be specified with
     * 'name_' prefix and needed store view suffix.
     *
     * For example:
     *  - 'name=All store views name' for all store views
     *  - 'name_specific_store=Specific store name' for store view with 'specific_store' store code
     *
     * @param Product $product
     * @return array
     */
    private function getBundleOptionTitles(Product $product): array
    {
        $optionCollections = $this->getProductOptionCollection($product);
        $optionsTitles = [];
        /** @var Option $option */
        foreach ($optionCollections->getItems() as $option) {
            $optionsTitles[$option->getId()]['name'] = $option->getTitle();
        }
        $storeIds = $product->getStoreIds();
        if (count($storeIds) > 1) {
            foreach ($storeIds as $storeId) {
                $optionCollections = $this->getProductOptionCollection($product, (int)$storeId);
                /** @var Option $option */
                foreach ($optionCollections->getItems() as $option) {
                    $optionTitle = $option->getTitle();
                    if ($optionsTitles[$option->getId()]['name'] != $optionTitle) {
                        $optionsTitles[$option->getId()]['name_' . $this->getStoreCodeById((int)$storeId)] =
                            $optionTitle;
                    }
                }
            }
        }
        return $optionsTitles;
    }

    /**
     * Get bundle product options selections data
     *
     * The selection price data for the global scope is stored under the 'price' and 'price_type' keys,
     * while for a specific website it is stored under
     * public the 'price_website_<website-code>' and 'price_type_website_<website-code>' keys.
     *
     * @param Product $product
     * @return array
     */
    private function getBundleOptionSelections(Product $product): array
    {
        $selections = $this->getBundleOptionSelectionsData($product);

        if (!$this->catalogData->isPriceGlobal()) {
            foreach ($product->getWebsiteIds() as $websiteId) {
                $websiteCode = $this->getWebsiteCodeById((int) $websiteId);
                $storeId = $this->getWebsiteDefaultStoreId((int) $websiteId);
                foreach ($this->getProductOptionCollection($product, $storeId) as $option) {
                    foreach ($option->getSelections() as $selection) {
                        $selectionData = $selections[$option->getOptionId()][$selection->getSelectionId()] ?? [];
                        if ($selectionData && $selection->getPriceScope() == $websiteId) {
                            $selections[$option->getOptionId()][$selection->getSelectionId()] = [
                                ...$selectionData,
                                'price_website_' . $websiteCode => $selection->getSelectionPriceValue(),
                                'price_type_website_' . $websiteCode =>
                                    $this->getPriceTypeValue($selection->getSelectionPriceType())
                            ];
                        }
                    }
                }
            }
        }

        return $selections;
    }

    /**
     * Get bundle product options selections data.
     *
     * @param Product $product
     * @param int $storeId
     * @return array
     */
    private function getBundleOptionSelectionsData(
        Product $product,
        int $storeId = Store::DEFAULT_STORE_ID
    ): array {
        $data = [];
        foreach ($this->getProductOptionCollection($product, $storeId) as $option) {
            /** @var Option $option*/
            foreach ($option->getSelections() as $selection) {
                /** @var Selection $selection*/
                $data[$option->getOptionId()][$selection->getSelectionId()] = [
                    'sku' => $selection->getSku(),
                    'price' => $selection->getSelectionPriceValue(),
                    'default' => $selection->getIsDefault(),
                    'default_qty' => $selection->getSelectionQty(),
                    'price_type' => $this->getPriceTypeValue($selection->getSelectionPriceType()),
                    'can_change_qty' => $selection->getSelectionCanChangeQty(),
                ];
            }
        }
        return $data;
    }

    /**
     * Get product options collection by provided product model.
     *
     * Set given store id to the product if it was defined (default store id will be set if was not).
     *
     * @param Product $product $product
     * @param int $storeId
     * @return \Magento\Bundle\Model\ResourceModel\Option\Collection
     */
    private function getProductOptionCollection(
        Product $product,
        int $storeId = Store::DEFAULT_STORE_ID
    ): \Magento\Bundle\Model\ResourceModel\Option\Collection {
        $productSku = $product->getSku();
        if (!isset($this->optionCollections[$productSku][$storeId])) {
            $product->unsetData($this->optionCollectionCacheKey);
            $product->setStoreId($storeId);
            $optionCollection = $product->getTypeInstance()
                ->getOptionsCollection($product)
                ->setOrder('position', Collection::SORT_ORDER_ASC);
            // Ensure children products are not filtered by website.
            // We need to export all children products regardless of the website they are assigned to.
            $product->getTypeInstance()->setStoreFilter(Store::DEFAULT_STORE_ID, $product);
            $selectionCollection = $product->getTypeInstance()
                ->getSelectionsCollection(
                    $product->getTypeInstance()->getOptionsIds($product),
                    $product
                )
                ->setOrder('position', Collection::SORT_ORDER_ASC)
                ->addAttributeToSort('position', Collection::SORT_ORDER_ASC);
            $optionCollection->appendSelections($selectionCollection, true);
            $this->optionCollections[$productSku][$storeId] = $optionCollection;
        }
        return $this->optionCollections[$productSku][$storeId];
    }

    /**
     * Retrieve default store id for website
     *
     * @param int $websiteId
     * @return int
     * @throws LocalizedException
     */
    private function getWebsiteDefaultStoreId(int $websiteId): int
    {
        return (int) $this->storeManager
            ->getGroup($this->storeManager->getWebsite($websiteId)->getDefaultGroupId())
            ->getDefaultStoreId();
    }

    /**
     * Retrieve website code by its ID.
     *
     * @param int $websiteId
     * @return string
     * @throws LocalizedException
     */
    private function getWebsiteCodeById(int $websiteId): string
    {
        return $this->storeManager->getWebsite($websiteId)->getCode();
    }

    /**
     * Retrieve store code by it's ID.
     *
     * Collect store id in $storeIdToCode[] private variable if it was not initialized earlier.
     *
     * @param int $storeId
     * @return string
     */
    private function getStoreCodeById(int $storeId): string
    {
        if (!isset($this->storeIdToCode[$storeId])) {
            $this->storeIdToCode[$storeId] = $this->storeManager->getStore($storeId)->getCode();
        }
        return $this->storeIdToCode[$storeId];
    }
}
