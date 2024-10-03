<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Fixture generator for Order entities with configurable number of different types of order items.
 *
 * Optionally generates inactive quotes for generated orders.
 *
 * Support the following format:
 * <!-- It is necessary to enable quotes for orders -->
 * <order_quotes_enable>{bool}</order_quotes_enable>
 *
 * <!-- Min number of simple products per each order -->
 * <order_simple_product_count_from>{int}</order_simple_product_count_from>
 *
 * <!-- Max number of simple products per each order -->
 * <order_simple_product_count_to>{int}</order_simple_product_count_to>
 *
 * <!-- Min number of configurable products per each order -->
 * <order_configurable_product_count_from>{int}</order_configurable_product_count_from>
 *
 * <!-- Max number of configurable products per each order -->
 * <order_configurable_product_count_to>{int}</order_configurable_product_count_to>
 *
 * <!-- Min number of big configurable products (with big amount of options) per each order -->
 * <order_big_configurable_product_count_from>{int}</order_big_configurable_product_count_from>
 *
 * <!-- Max number of big configurable products (with big amount of options) per each order -->
 * <order_big_configurable_product_count_to>{int}</order_big_configurable_product_count_to>
 *
 * <!-- Number of orders to generate -->
 * <orders>{int}</orders>
 *
 * @see setup/performance-toolkit/profiles/ce/small.xml
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrdersFixture extends Fixture
{
    /**
     * Batch size for order generation.
     *
     * @var string
     */
    const BATCH_SIZE = 1000;

    /**
     * Product type for "big" configurable products.
     *
     * @var string
     */
    const BIG_CONFIGURABLE_TYPE = 'big_configurable';

    /**
     * Default value for minimum items (simple) per order configuration.
     *
     * @var int
     */
    const ORDER_SIMPLE_PRODUCT_COUNT_FROM = 2;

    /**
     * Default value for maximum items (simple) per order configuration.
     *
     * @var int
     */
    const ORDER_SIMPLE_PRODUCT_COUNT_TO = 2;

    /**
     * Default value for minimum items (configurable) per order configuration.
     *
     * @var int
     */
    const ORDER_CONFIGURABLE_PRODUCT_COUNT_FROM = 0;

    /**
     * Default value for maximum items (configurable) per order configuration.
     *
     * @var int
     */
    const ORDER_CONFIGURABLE_PRODUCT_COUNT_TO = 0;

    /**
     * Default value for minimum items (big configurable) per order configuration.
     *
     * @var int
     */
    const ORDER_BIG_CONFIGURABLE_PRODUCT_COUNT_FROM = 0;

    /**
     * Default value for maximum items (big configurable) per order configuration.
     *
     * @var int
     */
    const ORDER_BIG_CONFIGURABLE_PRODUCT_COUNT_TO = 0;

    /**
     * Fixture execution priority.
     *
     * @var int
     */
    protected $priority = 135;

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
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\ConfigurableProduct\Api\OptionRepositoryInterface
     */
    private $optionRepository;

    /**
     * @var \Magento\ConfigurableProduct\Api\LinkManagementInterface
     */
    private $linkManagement;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * Flag specifies if inactive quotes should be generated for orders.
     *
     * @var bool
     */
    private $orderQuotesEnable = true;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\ConfigurableProduct\Api\OptionRepositoryInterface $optionRepository
     * @param \Magento\ConfigurableProduct\Api\LinkManagementInterface $linkManagement
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param FixtureModel $fixtureModel
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\ConfigurableProduct\Api\OptionRepositoryInterface $optionRepository,
        \Magento\ConfigurableProduct\Api\LinkManagementInterface $linkManagement,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        FixtureModel $fixtureModel
    ) {
        $this->storeManager = $storeManager;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->optionRepository = $optionRepository;
        $this->linkManagement = $linkManagement;
        $this->serializer = $serializer;
        parent::__construct($fixtureModel);
    }

    /**
     * @inheritdoc
     *
     * Design of Performance Fixture Generators require generator classes to override Fixture Model's execute method.
     *
     * @throws \Exception Any exception raised during DB query.
     * @return void
     * @SuppressWarnings(PHPMD)
     */
    public function execute()
    {
        $orderSimpleCountFrom = (int)$this->fixtureModel->getValue(
            'order_simple_product_count_from',
            self::ORDER_SIMPLE_PRODUCT_COUNT_FROM
        );
        $orderSimpleCountTo = (int)$this->fixtureModel->getValue(
            'order_simple_product_count_to',
            self::ORDER_SIMPLE_PRODUCT_COUNT_TO
        );
        $orderConfigurableCountFrom = (int)$this->fixtureModel->getValue(
            'order_configurable_product_count_from',
            self::ORDER_CONFIGURABLE_PRODUCT_COUNT_FROM
        );
        $orderConfigurableCountTo = (int)$this->fixtureModel->getValue(
            'order_configurable_product_count_to',
            self::ORDER_CONFIGURABLE_PRODUCT_COUNT_TO
        );
        $orderBigConfigurableCountFrom = (int)$this->fixtureModel->getValue(
            'order_big_configurable_product_count_from',
            self::ORDER_BIG_CONFIGURABLE_PRODUCT_COUNT_FROM
        );
        $orderBigConfigurableCountTo = (int)$this->fixtureModel->getValue(
            'order_big_configurable_product_count_to',
            self::ORDER_BIG_CONFIGURABLE_PRODUCT_COUNT_TO
        );
        $this->orderQuotesEnable = (bool)$this->fixtureModel->getValue('order_quotes_enable', true);

        $entityId = $this->getMaxEntityId(
            'sales_order',
            \Magento\Sales\Model\ResourceModel\Order::class,
            'entity_id'
        );
        $requestedOrders = (int)$this->fixtureModel->getValue('orders', 0);
        if ($requestedOrders - $entityId < 1) {
            return;
        }

        $ruleId = $this->getMaxEntityId(
            'salesrule',
            \Magento\SalesRule\Model\ResourceModel\Rule::class,
            'rule_id'
        );

        $maxItemId = $this->getMaxEntityId(
            'sales_order_item',
            \Magento\Sales\Model\ResourceModel\Order\Item::class,
            'item_id'
        );
        $maxItemsPerOrder = $orderSimpleCountTo + ($orderConfigurableCountTo + $orderBigConfigurableCountTo) * 2;

        /** @var \Generator $itemIdSequence */
        $itemIdSequence = $this->getItemIdSequence($maxItemId, $requestedOrders, $maxItemsPerOrder);

        $this->prepareQueryTemplates();

        $result = [];
        foreach ($this->storeManager->getStores() as $store) {
            $productsResult = [];
            $this->storeManager->setCurrentStore($store->getId());

            if ($orderSimpleCountTo > 0) {
                $productsResult[Type::TYPE_SIMPLE] = $this->prepareSimpleProducts(
                    $this->getProductIds($store, Type::TYPE_SIMPLE, $orderSimpleCountTo)
                );
            }
            if ($orderConfigurableCountTo > 0) {
                $productsResult[Configurable::TYPE_CODE] = $this->prepareConfigurableProducts(
                    $this->getProductIds($store, Configurable::TYPE_CODE, $orderConfigurableCountTo)
                );
            }
            if ($orderBigConfigurableCountTo > 0) {
                $productsResult[self::BIG_CONFIGURABLE_TYPE] = $this->prepareConfigurableProducts(
                    $this->getProductIds($store, self::BIG_CONFIGURABLE_TYPE, $orderBigConfigurableCountTo)
                );
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

        $productStoreId = function ($index) use ($result) {
            return $result[$index % count($result)][0];
        };
        $productStoreName = function ($index) use ($result) {
            return $result[$index % count($result)][1];
        };
        $productId = function ($entityId, $index, $type) use ($result) {
            return $result[$entityId % count($result)][2][$type][$index]['id'];
        };
        $productSku = function ($entityId, $index, $type) use ($result) {
            return $result[$entityId % count($result)][2][$type][$index]['sku'];
        };
        $productName = function ($entityId, $index, $type) use ($result) {
            return $result[$entityId % count($result)][2][$type][$index]['name'];
        };
        $productBuyRequest = function ($entityId, $index, $type) use ($result) {
            return $result[$entityId % count($result)][2][$type][$index]['buyRequest'];
        };
        $productChildBuyRequest = function ($entityId, $index, $type) use ($result) {
            return $result[$entityId % count($result)][2][$type][$index]['childBuyRequest'];
        };
        $productChildId = function ($entityId, $index, $type) use ($result) {
            return $result[$entityId % count($result)][2][$type][$index]['childId'];
        };

        $address = [
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

        $batchNumber = 0;
        $entityId++;
        while ($entityId <= $requestedOrders) {
            $batchNumber++;
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $productCount = [
                // mt_rand() here is not for cryptographic use.
                // phpcs:disable Magento2.Security.InsecureFunction
                Type::TYPE_SIMPLE => mt_rand($orderSimpleCountFrom, $orderSimpleCountTo),
                Configurable::TYPE_CODE => mt_rand($orderConfigurableCountFrom, $orderConfigurableCountTo),
                self::BIG_CONFIGURABLE_TYPE => mt_rand($orderBigConfigurableCountFrom, $orderBigConfigurableCountTo)
                // phpcs:enable
            ];
            $order = [
                '%itemsPerOrder%' => array_sum($productCount),
                '%orderNumber%' => 100000000 * $productStoreId($entityId) + $entityId,
                '%email%' => "order_{$entityId}@example.com",
                '%time%' => date(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
                '%productStoreId%' => $productStoreId($entityId),
                '%productStoreName%' => $productStoreName($entityId),
                '%entityId%' => $entityId,
                '%ruleId%' => $ruleId,
            ];
            $shippingAddress = ['%orderAddressId%' => $entityId * 2 - 1, '%addressType%' => 'shipping'];
            $billingAddress = ['%orderAddressId%' => $entityId * 2, '%addressType%' => 'billing'];

            try {
                $this->query('quote', $order);
                $this->query('quote_address', $order, $address, $shippingAddress);
                $this->query('quote_address', $order, $address, $billingAddress);
                $this->query('quote_payment', $order);
                $this->query('quote_shipping_rate', $order, $shippingAddress);

                $this->query('eav_entity_store', $order);
                $this->query('sales_order', $order);
                $this->query('sales_order_address', $order, $address, $shippingAddress);
                $this->query('sales_order_address', $order, $address, $billingAddress);
                $this->query('sales_order_grid', $order);
                $this->query('sales_order_payment', $order);
                $this->query('sales_order_status_history', $order);

                for ($i = 0; $i < $productCount[Type::TYPE_SIMPLE]; $i++) {
                    $itemData = [
                        '%productId%' => $productId($entityId, $i, Type::TYPE_SIMPLE),
                        '%sku%' => $productSku($entityId, $i, Type::TYPE_SIMPLE),
                        '%name%' => $productName($entityId, $i, Type::TYPE_SIMPLE),
                        '%itemId%' => $itemIdSequence->current(),
                        '%productType%' => Type::TYPE_SIMPLE,
                        '%productOptions%' => $productBuyRequest($entityId, $i, Type::TYPE_SIMPLE),
                        '%parentItemId%' => 'null',
                    ];
                    $this->query('sales_order_item', $order, $itemData);
                    $this->query('quote_item', $order, $itemData);
                    $this->query('quote_item_option', $order, $itemData, [
                        '%code%' => 'info_buyRequest',
                        '%value%' => $this->serializer->serialize([
                            'product' => $productId($entityId, $i, Type::TYPE_SIMPLE),
                            'qty' => "1",
                            'uenc' => 'aHR0cDovL21hZ2UyLmNvbS9jYXRlZ29yeS0xLmh0bWw'
                        ])
                    ]);
                    $itemIdSequence->next();
                }

                foreach ([Configurable::TYPE_CODE, self::BIG_CONFIGURABLE_TYPE] as $type) {
                    for ($i = 0; $i < $productCount[$type]; $i++) {
                        // Generate parent item
                        $parentItemId = $itemIdSequence->current();
                        $itemData = [
                            '%productId%' => $productId($entityId, $i, $type),
                            '%sku%' => $productSku($entityId, $i, $type),
                            '%name%' => $productName($entityId, $i, $type),
                            '%productOptions%' => $productBuyRequest($entityId, $i, $type)['order'],
                            '%itemId%' => $parentItemId,
                            '%parentItemId%' => 'null',
                            '%productType%' => Configurable::TYPE_CODE
                        ];
                        $this->query('sales_order_item', $order, $itemData);
                        $this->query('quote_item', $order, $itemData);
                        $this->query('quote_item_option', $order, $itemData, [
                            '%code%' => 'info_buyRequest',
                            '%value%' => $productBuyRequest($entityId, $i, $type)['quote']
                        ]);
                        $this->query('quote_item_option', $order, $itemData, [
                            '%code%' => 'attributes',
                            '%value%' => $productBuyRequest($entityId, $i, $type)['super_attribute']
                        ]);
                        $itemData['%productId%'] = $productChildId($entityId, $i, $type);
                        $this->query('quote_item_option', $itemData, [
                            '%code%' => "product_qty_" . $productChildId($entityId, $i, $type),
                            '%value%' => "1"
                        ]);
                        $this->query('quote_item_option', $itemData, [
                            '%code%' => "simple_product",
                            '%value%' => $productChildId($entityId, $i, $type)
                        ]);
                        $itemIdSequence->next();

                        // Generate child item
                        $itemData = [
                            '%productId%' => $productChildId($entityId, $i, $type),
                            '%sku%' => $productSku($entityId, $i, $type),
                            '%name%' => $productName($entityId, $i, $type),
                            '%productOptions%' => $productChildBuyRequest($entityId, $i, $type)['order'],
                            '%itemId%' => $itemIdSequence->current(),
                            '%parentItemId%' => $parentItemId,
                            '%productType%' => Type::TYPE_SIMPLE
                        ];

                        $this->query('sales_order_item', $order, $itemData);
                        $this->query('quote_item', $order, $itemData);
                        $this->query('quote_item_option', $itemData, [
                            '%code%' => "info_buyRequest",
                            '%value%' => $productChildBuyRequest($entityId, $i, $type)['quote']
                        ]);
                        $this->query('quote_item_option', $itemData, [
                            '%code%' => "parent_product_id",
                            '%value%' => $productId($entityId, $i, $type)
                        ]);
                        $itemIdSequence->next();
                    }
                }
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
            $entityId++;
        }

        foreach ($this->resourceConnections as $connection) {
            if ($connection->getTransactionLevel() > 0) {
                $connection->commit();
            }
        }
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
        $fileName = __DIR__ . DIRECTORY_SEPARATOR . "_files" . DIRECTORY_SEPARATOR . "orders_fixture_data.json";
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
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

            /** @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resourceModel */
            $resourceModel = $this->fixtureModel->getObjectManager()->get($resource);
            $connection = $resourceModel->getConnection();
            if ($connection->getTransactionLevel() == 0) {
                $connection->beginTransaction();
            }

            // phpcs:ignore Magento2.SQL.RawQuery
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
     * @param array $replacements
     * @return void
     */
    protected function query($table, ... $replacements)
    {
        if (!$this->orderQuotesEnable && strpos($table, "quote") !== false) {
            return;
        }
        $query = $this->queryTemplates[$table];
        foreach ($replacements as $data) {
            $query = str_replace(array_keys($data), array_values($data), $query);
        }

        $this->resourceConnections[$table]->query($query);
    }

    /**
     * Get maximum order id currently existing in the database.
     *
     * To support incremental generation of the orders it is necessary to get the maximum order entity_id currently
     * existing in the database.
     *
     * @param string $tableName
     * @param string $resourceName
     * @param string $column
     * @return int
     */
    private function getMaxEntityId($tableName, $resourceName, $column = 'entity_id')
    {
        $tableName = $this->getTableName(
            $tableName,
            $resourceName
        );

        /** @var \Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb $resource */
        $resource = $this->fixtureModel->getObjectManager()->get($resourceName);
        $connection = $resource->getConnection();
        // phpcs:ignore Magento2.SQL.RawQuery
        return (int)$connection->query("SELECT MAX(`{$column}`) FROM `{$tableName}`;")->fetchColumn(0);
    }

    /**
     * Get a limited amount of product id's from a collection filtered by store and specific product type.
     *
     * @param \Magento\Store\Api\Data\StoreInterface $store
     * @param string $typeId
     * @param int $limit
     * @return array
     * @throws \RuntimeException
     */
    private function getProductIds(\Magento\Store\Api\Data\StoreInterface $store, $typeId, $limit = null)
    {
        /** @var $productCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $productCollection = $this->productCollectionFactory->create();

        $productCollection->addStoreFilter($store->getId());
        $productCollection->addWebsiteFilter($store->getWebsiteId());

        // "Big%" should be replaced with a configurable value.
        if ($typeId === self::BIG_CONFIGURABLE_TYPE) {
            $productCollection->getSelect()->where(" type_id = '" . Configurable::TYPE_CODE . "' ");
            $productCollection->getSelect()->where(" sku LIKE 'Big%' ");
        } else {
            $productCollection->getSelect()->where(" type_id = '$typeId' ");
            $productCollection->getSelect()->where(" sku NOT LIKE 'Big%' ");
        }
        $ids = $productCollection->getAllIds($limit);
        if ($limit && count($ids) < $limit) {
            throw new \RuntimeException('Not enough products of type: ' . $typeId);
        }
        return $ids;
    }

    /**
     * Prepare data for the simple products to be used as order items.
     *
     * Based on the Product Id's load data, which is required to replace placeholders in queries.
     *
     * @param array $productIds
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
     * @param array $productIds
     * @return array
     */
    private function prepareConfigurableProducts(array $productIds = [])
    {
        $productsResult = [];
        foreach ($productIds as $key => $configurableId) {
            $configurableProduct = $this->productRepository->getById($configurableId);
            $options = $this->optionRepository->getList($configurableProduct->getSku());
            $configurableChild = $configurableProduct->getTypeInstance()->getUsedProducts($configurableProduct);
            $configurableChild = reset($configurableChild);
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
     * @inheritdoc
     */
    public function getActionTitle()
    {
        return 'Generating orders';
    }

    /**
     * @inheritdoc
     */
    public function introduceParamLabels()
    {
        return [
            'orders' => 'Orders'
        ];
    }

    /**
     * Get real table name for db table, validated by db adapter.
     *
     * In case prefix or other features mutating default table names are used.
     *
     * @param string $tableName
     * @param string $resourceName
     * @return string
     */
    public function getTableName($tableName, $resourceName)
    {
        /** @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource */
        $resource = $this->fixtureModel->getObjectManager()->get($resourceName);
        return $resource->getConnection()->getTableName($resource->getTable($tableName));
    }

    /**
     * Get sequence for order items
     *
     * @param int $maxItemId
     * @param int $requestedOrders
     * @param int $maxItemsPerOrder
     * @return \Generator
     */
    private function getItemIdSequence($maxItemId, $requestedOrders, $maxItemsPerOrder)
    {
        $requestedItems = $requestedOrders * $maxItemsPerOrder;
        for ($i = $maxItemId + 1; $i <= $requestedItems; $i++) {
            yield $i;
        }
    }
}
