<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures\Quote;

use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Fixture generator for Quote entities.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteGenerator
{
    /**
     * Batch size for quote generation.
     *
     * @var string
     */
    const BATCH_SIZE = 1000;

    /**
     * INSERT query templates.
     *
     * @var array
     */
    private $queryTemplates;

    /**
     * Array of resource connections ordered by tables.
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface[]
     */
    private $resourceConnections;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\ConfigurableProduct\Api\OptionRepositoryInterface
     */
    private $optionRepository;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var \Magento\ConfigurableProduct\Api\LinkManagementInterface
     */
    private $linkManagement;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @var array
     */
    private $productStubData;

    /**
     * @var QuoteConfiguration
     */
    private $config;

    /**
     * @var \Magento\Setup\Fixtures\FixtureModel
     */
    private $fixtureModel;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\ConfigurableProduct\Api\OptionRepositoryInterface $optionRepository
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\ConfigurableProduct\Api\LinkManagementInterface $linkManagement
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param QuoteConfiguration $config
     * @param \Magento\Setup\Fixtures\FixtureModel $fixtureModel
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\ConfigurableProduct\Api\OptionRepositoryInterface $optionRepository,
        \Magento\ConfigurableProduct\Api\LinkManagementInterface $linkManagement,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        QuoteConfiguration $config,
        \Magento\Setup\Fixtures\FixtureModel $fixtureModel
    ) {
        $this->storeManager = $storeManager;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->optionRepository = $optionRepository;
        $this->linkManagement = $linkManagement;
        $this->serializer = $serializer;
        $this->config = $config;
        $this->fixtureModel = $fixtureModel;
    }

    /**
     * Prepare and save quotes in database.
     *
     * @throws \Exception
     * @return void
     */
    public function generateQuotes()
    {
        $maxItemsPerOrder = $this->config->getSimpleCountTo()
            + ($this->config->getConfigurableCountTo() + $this->config->getBigConfigurableCountTo()) * 2;

        $maxItemId = $this->getMaxEntityId(
            'quote_item',
            \Magento\Quote\Model\ResourceModel\Quote\Item::class,
            'item_id'
        );
        /** @var \Generator $itemIdSequence */
        $itemIdSequence = $this->getItemIdSequence(
            $maxItemId,
            $this->config->getRequiredQuoteQuantity(),
            $maxItemsPerOrder
        );
        $this->productStubData = $this->prepareProductsForQuote();
        $this->prepareQueryTemplates();

        $entityId = $this->getMaxEntityId('quote', \Magento\Quote\Model\ResourceModel\Quote::class, 'entity_id');
        $quoteQty = $this->config->getExistsQuoteQuantity();
        $batchNumber = 0;
        while ($quoteQty < $this->config->getRequiredQuoteQuantity()) {
            $entityId++;
            $batchNumber++;
            $quoteQty++;

            try {
                $this->saveQuoteWithQuoteItems($entityId, $itemIdSequence);
            } catch (\Exception $lastException) {
                foreach ($this->resourceConnections as $connection) {
                    if ($connection->getTransactionLevel() > 0) {
                        $connection->rollBack();
                    }
                }
                throw $lastException;
            }

            if ($batchNumber >= self::BATCH_SIZE) {
                $this->commitBatch();
                $batchNumber = 0;
            }
        }

        foreach ($this->resourceConnections as $connection) {
            if ($connection->getTransactionLevel() > 0) {
                $connection->commit();
            }
        }
    }

    /**
     * Save quote and quote items.
     *
     * @param int $entityId
     * @param \Generator $itemIdSequence
     * @return void
     */
    private function saveQuoteWithQuoteItems($entityId, \Generator $itemIdSequence)
    {
        $productCount = [
            Type::TYPE_SIMPLE => mt_rand(
                $this->config->getSimpleCountFrom(),
                $this->config->getSimpleCountTo()
            ),
            Configurable::TYPE_CODE => mt_rand(
                $this->config->getConfigurableCountFrom(),
                $this->config->getConfigurableCountTo()
            ),
            QuoteConfiguration::BIG_CONFIGURABLE_TYPE => mt_rand(
                $this->config->getBigConfigurableCountFrom(),
                $this->config->getBigConfigurableCountTo()
            )
        ];
        $quote = [
            '%itemsPerOrder%' => array_sum($productCount),
            '%orderNumber%' => 100000000 * $this->getStubProductStoreId($entityId) + $entityId,
            '%email%' => "quote_{$entityId}@example.com",
            '%time%' => date(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
            '%productStoreId%' => $this->getStubProductStoreId($entityId),
            '%productStoreName%' => $this->getStubProductStoreName($entityId),
            '%entityId%' => $entityId,
        ];
        $shippingAddress = ['%orderAddressId%' => $entityId * 2 - 1, '%addressType%' => 'shipping'];
        $billingAddress = ['%orderAddressId%' => $entityId * 2, '%addressType%' => 'billing'];
        $address = $this->getAddressDataFixture();

        $this->query('quote', $quote);
        $this->query('quote_address', $quote, $address, $shippingAddress);
        $this->query('quote_address', $quote, $address, $billingAddress);

        for ($i = 0; $i < $productCount[Type::TYPE_SIMPLE]; $i++) {
            $this->saveItemSimpleData($entityId, $i, $itemIdSequence->current(), $quote);
            $itemIdSequence->next();
        }

        foreach ([Configurable::TYPE_CODE, QuoteConfiguration::BIG_CONFIGURABLE_TYPE] as $type) {
            for ($i = 0; $i < $productCount[$type]; $i++) {
                // Generate parent item
                $parentItemId = $itemIdSequence->current();
                $this->saveParentItemConfigurableData($entityId, $i, $parentItemId, $type, $quote);
                $itemIdSequence->next();

                // Generate child item
                $itemId = $itemIdSequence->current();
                $this->saveChildItemConfigurable($entityId, $i, $itemId, $parentItemId, $type, $quote);
                $itemIdSequence->next();
            }
        }
    }

    /**
     * Prepare and save quote item with simple product.
     *
     * @param int $entityId
     * @param int $index
     * @param int $itemId
     * @param array $quote
     * @return void
     */
    private function saveItemSimpleData($entityId, $index, $itemId, array $quote)
    {
        $itemData = [
            '%productId%' => $this->getStubProductId($entityId, $index, Type::TYPE_SIMPLE),
            '%sku%' => $this->getStubProductSku($entityId, $index, Type::TYPE_SIMPLE),
            '%name%' => $this->getStubProductName($entityId, $index, Type::TYPE_SIMPLE),
            '%itemId%' => $itemId,
            '%productType%' => Type::TYPE_SIMPLE,
            '%productOptions%' => $this->getStubProductBuyRequest($entityId, $index, Type::TYPE_SIMPLE),
            '%parentItemId%' => 'null',
        ];
        $this->query('quote_item', $quote, $itemData);
        $this->query('quote_item_option', $quote, $itemData, [
            '%code%' => 'info_buyRequest',
            '%value%' => $this->serializer->serialize([
                'product' => $this->getStubProductId($entityId, $index, Type::TYPE_SIMPLE),
                'qty' => "1",
                'uenc' => 'aHR0cDovL21hZ2UyLmNvbS9jYXRlZ29yeS0xLmh0bWw'
            ])
        ]);
    }

    /**
     * Prepare and save parent quote item for configurable product.
     *
     * @param int $entityId
     * @param int $index
     * @param int $parentItemId
     * @param string $productType
     * @param array $quote
     * @return void
     */
    private function saveParentItemConfigurableData($entityId, $index, $parentItemId, $productType, array $quote)
    {
        $itemData = [
            '%productId%' => $this->getStubProductId($entityId, $index, $productType),
            '%sku%' => $this->getStubProductSku($entityId, $index, $productType),
            '%name%' => $this->getStubProductName($entityId, $index, $productType),
            '%productOptions%' => $this->getStubProductBuyRequest($entityId, $index, $productType)['order'],
            '%itemId%' => $parentItemId,
            '%parentItemId%' => 'null',
            '%productType%' => Configurable::TYPE_CODE
        ];
        $this->query('quote_item', $quote, $itemData);
        $this->query('quote_item_option', $quote, $itemData, [
            '%code%' => 'info_buyRequest',
            '%value%' => $this->getStubProductBuyRequest($entityId, $index, $productType)['quote']
        ]);
        $this->query('quote_item_option', $quote, $itemData, [
            '%code%' => 'attributes',
            '%value%' => $this->getStubProductBuyRequest($entityId, $index, $productType)['super_attribute']
        ]);
        $itemData['%productId%'] = $this->getStubProductChildId($entityId, $index, $productType);
        $this->query('quote_item_option', $itemData, [
            '%code%' => "product_qty_" . $this->getStubProductChildId($entityId, $index, $productType),
            '%value%' => "1"
        ]);
        $this->query('quote_item_option', $itemData, [
            '%code%' => "simple_product",
            '%value%' => $this->getStubProductChildId($entityId, $index, $productType)
        ]);
    }

    /**
     * Prepare and save child quote item for configurable product.
     *
     * @param int $entityId
     * @param int $index
     * @param int $itemId
     * @param int $parentItemId
     * @param string $productType
     * @param array $quote
     * @return void
     */
    private function saveChildItemConfigurable($entityId, $index, $itemId, $parentItemId, $productType, array $quote)
    {
        $itemData = [
            '%productId%' => $this->getStubProductChildId($entityId, $index, $productType),
            '%sku%' => $this->getStubProductSku($entityId, $index, $productType),
            '%name%' => $this->getStubProductName($entityId, $index, $productType),
            '%productOptions%' => $this->getStubProductChildBuyRequest($entityId, $index, $productType)['order'],
            '%itemId%' => $itemId,
            '%parentItemId%' => $parentItemId,
            '%productType%' => Type::TYPE_SIMPLE
        ];

        $this->query('quote_item', $quote, $itemData);
        $this->query('quote_item_option', $itemData, [
            '%code%' => "info_buyRequest",
            '%value%' => $this->getStubProductChildBuyRequest($entityId, $index, $productType)['quote']
        ]);
        $this->query('quote_item_option', $itemData, [
            '%code%' => "parent_product_id",
            '%value%' => $this->getStubProductId($entityId, $index, $productType)
        ]);
    }

    /**
     * Get store id for quote item by product index.
     *
     * @param int $entityId
     * @return int
     */
    private function getStubProductStoreId($entityId)
    {
        return $this->productStubData[$this->getProductStubIndex($entityId)][0];
    }

    /**
     * Get store name for quote item by product index.
     *
     * @param int $entityId
     * @return string
     */
    private function getStubProductStoreName($entityId)
    {
        return $this->productStubData[$this->getProductStubIndex($entityId)][1];
    }

    /**
     * Get product id for quote item by product index.
     *
     * @param int $entityId
     * @param int $index
     * @param string $type
     * @return int
     */
    private function getStubProductId($entityId, $index, $type)
    {
        return $this->productStubData[$this->getProductStubIndex($entityId)][2][$type][$index]['id'];
    }

    /**
     * Get product SKU for quote item by product index.
     *
     * @param int $entityId
     * @param int $index
     * @param string $type
     * @return string
     */
    private function getStubProductSku($entityId, $index, $type)
    {
        return $this->productStubData[$this->getProductStubIndex($entityId)][2][$type][$index]['sku'];
    }

    /**
     * Get product name for quote item by product index.
     *
     * @param int $entityId
     * @param int $index
     * @param string $type
     * @return string
     */
    private function getStubProductName($entityId, $index, $type)
    {
        return $this->productStubData[$this->getProductStubIndex($entityId)][2][$type][$index]['name'];
    }

    /**
     * Get product buy request for quote item by product index.
     *
     * @param int $entityId
     * @param int $index
     * @param string $type
     * @return string
     */
    private function getStubProductBuyRequest($entityId, $index, $type)
    {
        return $this->productStubData[$this->getProductStubIndex($entityId)][2][$type][$index]['buyRequest'];
    }

    /**
     * Get configurable product child id for quote item by product index.
     *
     * @param int $entityId
     * @param int $index
     * @param string $type
     * @return string
     */
    private function getStubProductChildBuyRequest($entityId, $index, $type)
    {
        return $this->productStubData[$this->getProductStubIndex($entityId)][2][$type][$index]['childBuyRequest'];
    }

    /**
     * Get configurable product child id for quote item by product index.
     *
     * @param int $entityId
     * @param int $index
     * @param string $type
     * @return int
     */
    private function getStubProductChildId($entityId, $index, $type)
    {
        return $this->productStubData[$this->getProductStubIndex($entityId)][2][$type][$index]['childId'];
    }

    /**
     * Get index of item in product stub array.
     *
     * @param int $entityId
     * @return int
     */
    private function getProductStubIndex($entityId)
    {
        $storeCount = count($this->productStubData);
        $qty = intdiv($this->config->getRequiredQuoteQuantity(), $storeCount);
        return intdiv($entityId, $qty) % $storeCount;
    }

    /**
     * Get quote address mock data.
     *
     * @return array
     */
    private function getAddressDataFixture()
    {
        return [
            '%firstName%' => 'First Name',
            '%lastName%' => 'Last Name',
            '%company%' => 'Company',
            '%address%' => 'Address',
            '%city%' => 'city',
            '%state%' => 'Alabama',
            '%country%' => 'US',
            '%zip%' => '11111',
            '%phone%' => '911'
        ];
    }

    /**
     * Prepare mock of products for quotes.
     *
     * @return array
     */
    private function prepareProductsForQuote()
    {
        $result = [];

        foreach ($this->storeManager->getStores() as $store) {
            $productsResult = [];
            $this->storeManager->setCurrentStore($store->getId());

            if ($this->config->getSimpleCountTo() > 0) {
                $productsResult[Type::TYPE_SIMPLE] = $this->prepareSimpleProducts(
                    $this->getProductIds($store, Type::TYPE_SIMPLE, $this->config->getSimpleCountTo())
                );
            }
            $configurables = [
                Configurable::TYPE_CODE => $this->config->getConfigurableCountTo(),
                QuoteConfiguration::BIG_CONFIGURABLE_TYPE => $this->config->getBigConfigurableCountTo(),
            ];

            foreach ($configurables as $type => $qty) {
                if ($qty > 0) {
                    $productsResult[$type] = $this->prepareConfigurableProducts(
                        $this->getProductIds(
                            $store,
                            $type,
                            $qty
                        )
                    );
                }
            }

            $result[] = [
                $store->getId(),
                implode(PHP_EOL, [
                    $this->storeManager->getWebsite($store->getWebsiteId())->getName(),
                    $this->storeManager->getGroup($store->getStoreGroupId())->getName(),
                    $store->getName()
                ]),
                $productsResult
            ];
        }

        return $result;
    }

    /**
     * Load and prepare INSERT query templates data from external file.
     *
     * Queries are prepared using external json file, where keys are DB column names and values represent data,
     * to be inserted to the table. Data may contain a default value or a placeholder which is replaced later during
     * flow (in the query method of this class).
     * Additionally, in case if multiple DB connections are set up, transaction is started for each connection.
     *
     * @return void
     */
    private function prepareQueryTemplates()
    {
        $fileName = $this->config->getFixtureDataFilename();
        $templateData = json_decode(file_get_contents(realpath($fileName)), true);
        foreach ($templateData as $table => $template) {
            if (isset($template['_table'])) {
                $table = $template['_table'];
                unset($template['_table']);
            }
            if (isset($template['_resource'])) {
                $resource = $template['_resource'];
                unset($template['_resource']);
            } else {
                $resource = explode("_", $table);
                foreach ($resource as &$item) {
                    $item = ucfirst($item);
                }
                $resource = "Magento\\"
                    . array_shift($resource)
                    . "\\Model\\ResourceModel\\"
                    . implode("\\", $resource);
            }

            $tableName = $this->getTableName($table, $resource);

            $querySuffix = "";
            if (isset($template['_query_suffix'])) {
                $querySuffix = $template['_query_suffix'];
                unset($template['_query_suffix']);
            }

            $fields = implode(', ', array_keys($template));
            $values = implode(', ', array_values($template));

            $connection = $this->getConnection($resource);
            if ($connection->getTransactionLevel() == 0) {
                $connection->beginTransaction();
            }

            $this->queryTemplates[$table] = "INSERT INTO `{$tableName}` ({$fields}) VALUES ({$values}){$querySuffix};";
            $this->resourceConnections[$table] = $connection;
        }
    }

    /**
     * Build and execute query.
     *
     * Builds a database query by replacing placeholder values in the cached queries and executes query in appropriate
     * DB connection (if setup). Additionally filters out quote-related queries, if appropriate flag is set.
     *
     * @param string $table
     * @param array ...$replacements
     * @return void
     */
    protected function query($table, ... $replacements)
    {
        $query = $this->queryTemplates[$table];
        foreach ($replacements as $data) {
            $query = str_replace(array_keys($data), array_values($data), $query);
        }

        $this->resourceConnections[$table]->query($query);
    }

    /**
     * Get maximum order id currently existing in the database.
     *
     * To support incremental generation of the orders it is necessary to get the maximum order entity_id currently.
     * existing in the database.
     *
     * @param string $tableName
     * @param string $resourceName
     * @param string $column [optional]
     * @return int
     */
    private function getMaxEntityId($tableName, $resourceName, $column = 'entity_id')
    {
        $tableName = $this->getTableName($tableName, $resourceName);
        $connection = $this->getConnection($resourceName);
        return (int)$connection->query("SELECT MAX(`{$column}`) FROM `{$tableName}`;")->fetchColumn(0);
    }

    /**
     * Get a limited amount of product id's from a collection filtered by store and specific product type.
     *
     * @param \Magento\Store\Api\Data\StoreInterface $store
     * @param string $typeId
     * @param int $limit [optional]
     * @return array
     */
    private function getProductIds(\Magento\Store\Api\Data\StoreInterface $store, $typeId, $limit = null)
    {
        /** @var $productCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addStoreFilter($store->getId());
        $productCollection->addWebsiteFilter($store->getWebsiteId());

        // "Big%" should be replaced with a configurable value.
        if ($typeId === QuoteConfiguration::BIG_CONFIGURABLE_TYPE) {
            $productCollection->getSelect()->where(" type_id = '" . Configurable::TYPE_CODE . "' ");
            $productCollection->getSelect()->where(" sku LIKE 'Big%' ");
        } else {
            $productCollection->getSelect()->where(" type_id = '$typeId' ");
            $productCollection->getSelect()->where(" sku NOT LIKE 'Big%' ");
        }

        return $productCollection->getAllIds($limit);
    }

    /**
     * Prepare data for the simple products to be used as order items.
     *
     * Based on the Product Id's load data, which is required to replace placeholders in queries.
     *
     * @param array $productIds [optional]
     * @return array
     */
    private function prepareSimpleProducts(array $productIds = [])
    {
        $productsResult = [];
        foreach ($productIds as $key => $simpleId) {
            $simpleProduct = $this->productRepository->getById($simpleId);
            $productsResult[$key]['id'] = $simpleId;
            $productsResult[$key]['sku'] = $simpleProduct->getSku();
            $productsResult[$key]['name'] = $simpleProduct->getName();
            $productsResult[$key]['buyRequest'] = $this->serializer->serialize([
                "info_buyRequest" => [
                    "uenc" => "aHR0cDovL21hZ2VudG8uZGV2L2NvbmZpZ3VyYWJsZS1wcm9kdWN0LTEuaHRtbA,,",
                    "product" => $simpleId,
                    "qty" => "1"
                ]
            ]);
        }
        return $productsResult;
    }

    /**
     * Prepare data for the configurable products to be used as order items.
     *
     * Based on the Product Id's load data, which is required to replace placeholders in queries.
     *
     * @param array $productIds [optional]
     * @return array
     */
    private function prepareConfigurableProducts(array $productIds = [])
    {
        $productsResult = [];
        foreach ($productIds as $key => $configurableId) {
            $configurableProduct = $this->productRepository->getById($configurableId);
            $options = $this->optionRepository->getList($configurableProduct->getSku());
            $configurableChild = $this->linkManagement->getChildren($configurableProduct->getSku())[0];
            $simpleSku = $configurableChild->getSku();
            $simpleId = $this->productRepository->get($simpleSku)->getId();

            $attributesInfo = [];
            $superAttribute = [];
            foreach ($options as $option) {
                $attributesInfo[] = [
                    "label" => $option->getLabel(),
                    "value" => $option['options']['0']['label'],
                    "option_id" => $option->getAttributeId(),
                    "option_value" => $option->getValues()[0]->getValueIndex()
                ];
                $superAttribute[$option->getAttributeId()] = $option->getValues()[0]->getValueIndex();
            }

            $configurableBuyRequest = [
                "info_buyRequest" => [
                    "uenc" => "aHR0cDovL21hZ2UyLmNvbS9jYXRlZ29yeS0xLmh0bWw",
                    "product" => $configurableId,
                    "selected_configurable_option" => $simpleId,
                    "related_product" => "",
                    "super_attribute" => $superAttribute,
                    "qty" => 1
                ],
                "attributes_info" => $attributesInfo,
                "simple_name" => $configurableChild->getName(),
                "simple_sku" => $configurableChild->getSku(),
            ];
            $simpleBuyRequest = [
                "info_buyRequest" => [
                    "uenc" => "aHR0cDovL21hZ2VudG8uZGV2L2NvbmZpZ3VyYWJsZS1wcm9kdWN0LTEuaHRtbA,,",
                    "product" => $configurableId,
                    "selected_configurable_option" => $simpleId,
                    "related_product" => "",
                    "super_attribute" => $superAttribute,
                    "qty" => "1"
                ]
            ];

            $quoteConfigurableBuyRequest = $configurableBuyRequest['info_buyRequest'];
            $quoteSimpleBuyRequest = $simpleBuyRequest['info_buyRequest'];
            unset($quoteConfigurableBuyRequest['selected_configurable_option']);
            unset($quoteSimpleBuyRequest['selected_configurable_option']);

            $productsResult[$key]['id'] = $configurableId;
            $productsResult[$key]['sku'] = $simpleSku;
            $productsResult[$key]['name'] = $configurableProduct->getName();
            $productsResult[$key]['childId'] = $simpleId;
            $productsResult[$key]['buyRequest'] = [
                'order' => $this->serializer->serialize($configurableBuyRequest),
                'quote' => $this->serializer->serialize($quoteConfigurableBuyRequest),
                'super_attribute' => $this->serializer->serialize($superAttribute)
            ];
            $productsResult[$key]['childBuyRequest'] = [
                'order' => $this->serializer->serialize($simpleBuyRequest),
                'quote' => $this->serializer->serialize($quoteSimpleBuyRequest),
            ];
        }
        return $productsResult;
    }

    /**
     * Commit all active transactions at the end of the batch.
     *
     * Many transactions may exist, since generation process creates a transaction per each available DB connection.
     *
     * @return void
     */
    private function commitBatch()
    {
        foreach ($this->resourceConnections as $connection) {
            if ($connection->getTransactionLevel() > 0) {
                $connection->commit();
                $connection->beginTransaction();
            }
        }
    }

    /**
     * Get sequence for order items.
     *
     * @param int $maxItemId
     * @param int $requestedOrders
     * @param int $maxItemsPerOrder
     * @return \Generator
     */
    private function getItemIdSequence($maxItemId, $requestedOrders, $maxItemsPerOrder)
    {
        $requestedItems = $maxItemId + ($requestedOrders + 1) * $maxItemsPerOrder;
        for ($i = $maxItemId + 1; $i <= $requestedItems; $i++) {
            yield $i;
        }
    }

    /**
     * Get real table name for db table, validated by db adapter.
     * In case prefix or other features mutating default table names are used.
     *
     * @param string $tableName
     * @param string $resourceName
     * @return string
     */
    private function getTableName($tableName, $resourceName)
    {
        /** @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource */
        $resource = $this->fixtureModel->getObjectManager()->get($resourceName);
        return $this->getConnection($resourceName)->getTableName($resource->getTable($tableName));
    }

    /**
     * Get connection to database for specified resource.
     *
     * @param string $resourceName
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection($resourceName)
    {
        $resource = $this->fixtureModel->getObjectManager()->get($resourceName);
        return $resource->getConnection();
    }
}
