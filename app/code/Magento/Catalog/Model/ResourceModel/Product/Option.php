<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Catalog product custom option resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Option extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    protected $metadataPool;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Currency factory
     *
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $_currencyFactory;

    /**
     * Core config model
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_config;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        $connectionName = null
    ) {
        $this->_currencyFactory = $currencyFactory;
        $this->_storeManager = $storeManager;
        $this->_config = $config;
        parent::__construct($context, $connectionName);
    }

    /**
     * Define main table and initialize connection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_product_option', 'option_id');
    }

    /**
     * Save options store data
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->_saveValuePrices($object);
        $this->_saveValueTitles($object);

        return parent::_afterSave($object);
    }

    /**
     * Save value prices
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _saveValuePrices(\Magento\Framework\Model\AbstractModel $object)
    {
        $priceTable = $this->getTable('catalog_product_option_price');
        $connection = $this->getConnection();

        /*
         * Better to check param 'price' and 'price_type' for saving.
         * If there is not price skip saving price
         */

        if ($object->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_FIELD ||
            $object->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_AREA ||
            $object->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_FILE ||
            $object->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DATE ||
            $object->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DATE_TIME ||
            $object->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_TIME
        ) {
            //save for store_id = 0
            if (!$object->getData('scope', 'price')) {
                $statement = $connection->select()->from(
                    $priceTable,
                    'option_id'
                )->where(
                    'option_id = ?',
                    $object->getId()
                )->where(
                    'store_id = ?',
                    \Magento\Store\Model\Store::DEFAULT_STORE_ID
                );
                $optionId = $connection->fetchOne($statement);

                if ($optionId) {
                    $data = $this->_prepareDataForTable(
                        new \Magento\Framework\DataObject(
                            ['price' => $object->getPrice(), 'price_type' => $object->getPriceType()]
                        ),
                        $priceTable
                    );

                    $connection->update(
                        $priceTable,
                        $data,
                        [
                            'option_id = ?' => $object->getId(),
                            'store_id  = ?' => \Magento\Store\Model\Store::DEFAULT_STORE_ID
                        ]
                    );
                } else {
                    $data = $this->_prepareDataForTable(
                        new \Magento\Framework\DataObject(
                            [
                                'option_id' => $object->getId(),
                                'store_id' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                                'price' => $object->getPrice(),
                                'price_type' => $object->getPriceType(),
                            ]
                        ),
                        $priceTable
                    );
                    $connection->insert($priceTable, $data);
                }
            }

            $scope = (int)$this->_config->getValue(
                \Magento\Store\Model\Store::XML_PATH_PRICE_SCOPE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            if ($object->getStoreId() != '0' && $scope == \Magento\Store\Model\Store::PRICE_SCOPE_WEBSITE) {
                $baseCurrency = $this->_config->getValue(
                    \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                    'default'
                );

                $storeIds = $this->_storeManager->getStore($object->getStoreId())->getWebsite()->getStoreIds();
                if (is_array($storeIds)) {
                    foreach ($storeIds as $storeId) {
                        if ($object->getPriceType() == 'fixed') {
                            $storeCurrency = $this->_storeManager->getStore($storeId)->getBaseCurrencyCode();
                            $rate = $this->_currencyFactory->create()->load($baseCurrency)->getRate($storeCurrency);
                            if (!$rate) {
                                $rate = 1;
                            }
                            $newPrice = $object->getPrice() * $rate;
                        } else {
                            $newPrice = $object->getPrice();
                        }

                        $statement = $connection->select()->from(
                            $priceTable
                        )->where(
                            'option_id = ?',
                            $object->getId()
                        )->where(
                            'store_id  = ?',
                            $storeId
                        );

                        if ($connection->fetchOne($statement)) {
                            $data = $this->_prepareDataForTable(
                                new \Magento\Framework\DataObject(
                                    ['price' => $newPrice, 'price_type' => $object->getPriceType()]
                                ),
                                $priceTable
                            );

                            $connection->update(
                                $priceTable,
                                $data,
                                ['option_id = ?' => $object->getId(), 'store_id  = ?' => $storeId]
                            );
                        } else {
                            $data = $this->_prepareDataForTable(
                                new \Magento\Framework\DataObject(
                                    [
                                        'option_id' => $object->getId(),
                                        'store_id' => $storeId,
                                        'price' => $newPrice,
                                        'price_type' => $object->getPriceType(),
                                    ]
                                ),
                                $priceTable
                            );
                            $connection->insert($priceTable, $data);
                        }
                    }
                }
            } elseif ($scope == \Magento\Store\Model\Store::PRICE_SCOPE_WEBSITE && $object->getData('scope', 'price')
            ) {
                $connection->delete(
                    $priceTable,
                    ['option_id = ?' => $object->getId(), 'store_id  = ?' => $object->getStoreId()]
                );
            }
        }

        return $this;
    }

    /**
     * Save titles
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _saveValueTitles(\Magento\Framework\Model\AbstractModel $object)
    {
        $connection = $this->getConnection();
        $titleTableName = $this->getTable('catalog_product_option_title');
        foreach ([\Magento\Store\Model\Store::DEFAULT_STORE_ID, $object->getStoreId()] as $storeId) {
            $existInCurrentStore = $this->getColFromOptionTable($titleTableName, (int)$object->getId(), (int)$storeId);
            $existInDefaultStore = (int)$storeId == \Magento\Store\Model\Store::DEFAULT_STORE_ID ?
                $existInCurrentStore :
                $this->getColFromOptionTable(
                    $titleTableName,
                    (int)$object->getId(),
                    \Magento\Store\Model\Store::DEFAULT_STORE_ID
                );

            if ($object->getTitle()) {
                $isDeleteStoreTitle = (bool)$object->getData('is_delete_store_title');
                if ($existInCurrentStore) {
                    if ($isDeleteStoreTitle && (int)$storeId != \Magento\Store\Model\Store::DEFAULT_STORE_ID) {
                        $connection->delete($titleTableName, ['option_title_id = ?' => $existInCurrentStore]);

                    } elseif ($object->getStoreId() == $storeId) {
                        $data = $this->_prepareDataForTable(
                            new \Magento\Framework\DataObject(['title' => $object->getTitle()]),
                            $titleTableName
                        );
                        $connection->update(
                            $titleTableName,
                            $data,
                            [
                                'option_id = ?' => $object->getId(),
                                'store_id  = ?' => $storeId,
                            ]
                        );
                    }
                } else {
                    // we should insert record into not default store only of if it does not exist in default store
                    if (
                        ($storeId == \Magento\Store\Model\Store::DEFAULT_STORE_ID && !$existInDefaultStore) ||
                        (
                            $storeId != \Magento\Store\Model\Store::DEFAULT_STORE_ID &&
                            !$existInCurrentStore &&
                            !$isDeleteStoreTitle
                        )
                    ) {
                        $data = $this->_prepareDataForTable(
                            new \Magento\Framework\DataObject(
                                [
                                    'option_id' => $object->getId(),
                                    'store_id' => $storeId,
                                    'title' => $object->getTitle(),
                                ]
                            ),
                            $titleTableName
                        );
                        $connection->insert($titleTableName, $data);
                    }
                }
            } else {
                if ($object->getId() && $object->getStoreId() > \Magento\Store\Model\Store::DEFAULT_STORE_ID
                    && $storeId
                ) {
                    $connection->delete(
                        $titleTableName,
                        [
                            'option_id = ?' => $object->getId(),
                            'store_id  = ?' => $object->getStoreId(),
                        ]
                    );
                }
            }
        }
    }

    /**
     * Get first col from from first row for option table
     *
     * @param string $tableName
     * @param int $optionId
     * @param int $storeId
     * @return string
     */
    protected function getColFromOptionTable($tableName, $optionId, $storeId)
    {
        $connection = $this->getConnection();
        $statement = $connection->select()->from(
            $tableName
        )->where(
            'option_id = ?',
            $optionId
        )->where(
            'store_id  = ?',
            $storeId
        );

        return $connection->fetchOne($statement);
    }

    /**
     * Delete prices
     *
     * @param int $optionId
     * @return $this
     */
    public function deletePrices($optionId)
    {
        $this->getConnection()->delete(
            $this->getTable('catalog_product_option_price'),
            ['option_id = ?' => $optionId]
        );

        return $this;
    }

    /**
     * Delete titles
     *
     * @param int $optionId
     * @return $this
     */
    public function deleteTitles($optionId)
    {
        $this->getConnection()->delete(
            $this->getTable('catalog_product_option_title'),
            ['option_id = ?' => $optionId]
        );

        return $this;
    }

    /**
     * Duplicate custom options for product
     *
     * @param \Magento\Catalog\Model\Product\Option $object
     * @param int $oldProductId
     * @param int $newProductId
     * @return \Magento\Catalog\Model\Product\Option
     */
    public function duplicate(\Magento\Catalog\Model\Product\Option $object, $oldProductId, $newProductId)
    {
        $connection = $this->getConnection();

        $optionsCond = [];
        $optionsData = [];

        // read and prepare original product options
        $select = $connection->select()->from(
            $this->getTable('catalog_product_option')
        )->where(
            'product_id = ?',
            $oldProductId
        );

        $query = $connection->query($select);

        while ($row = $query->fetch()) {
            $optionsData[$row['option_id']] = $row;
            $optionsData[$row['option_id']]['product_id'] = $newProductId;
            unset($optionsData[$row['option_id']]['option_id']);
        }

        // insert options to duplicated product
        foreach ($optionsData as $oId => $data) {
            $connection->insert($this->getMainTable(), $data);
            $optionsCond[$oId] = $connection->lastInsertId($this->getMainTable());
        }

        // copy options prefs
        foreach ($optionsCond as $oldOptionId => $newOptionId) {
            // title
            $table = $this->getTable('catalog_product_option_title');

            $select = $this->getConnection()->select()->from(
                $table,
                [new \Zend_Db_Expr($newOptionId), 'store_id', 'title']
            )->where(
                'option_id = ?',
                $oldOptionId
            );

            $insertSelect = $connection->insertFromSelect(
                $select,
                $table,
                ['option_id', 'store_id', 'title'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
            );
            $connection->query($insertSelect);

            // price
            $table = $this->getTable('catalog_product_option_price');

            $select = $connection->select()->from(
                $table,
                [new \Zend_Db_Expr($newOptionId), 'store_id', 'price', 'price_type']
            )->where(
                'option_id = ?',
                $oldOptionId
            );

            $insertSelect = $connection->insertFromSelect(
                $select,
                $table,
                ['option_id', 'store_id', 'price', 'price_type'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
            );
            $connection->query($insertSelect);

            $object->getValueInstance()->duplicate($oldOptionId, $newOptionId);
        }

        return $object;
    }

    /**
     * Retrieve option searchable data
     *
     * @param int $productId
     * @param int $storeId
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getSearchableData($productId, $storeId)
    {
        $searchData = [];
        $connection = $this->getConnection();
        $titleCheckSql = $connection->getCheckSql(
            'option_title_store.title IS NULL',
            'option_title_default.title',
            'option_title_store.title'
        );

        // retrieve options title
        $defaultOptionJoin = implode(
            ' AND ',
            [
                'option_title_default.option_id=product_option.option_id',
                $connection->quoteInto(
                    'option_title_default.store_id = ?',
                    \Magento\Store\Model\Store::DEFAULT_STORE_ID
                )
            ]
        );

        $storeOptionJoin = implode(
            ' AND ',
            [
                'option_title_store.option_id=product_option.option_id',
                $connection->quoteInto('option_title_store.store_id = ?', (int)$storeId)
            ]
        );

        $select = $connection->select()->from(
            ['product_option' => $this->getMainTable()],
            null
        )->join(
            ['option_title_default' => $this->getTable('catalog_product_option_title')],
            $defaultOptionJoin,
            []
        )->join(
            ['cpe' => $this->getTable('catalog_product_entity')],
            sprintf(
                'cpe.%s = product_option.product_id',
                $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField()
            ),
            []
        )->joinLeft(
            ['option_title_store' => $this->getTable('catalog_product_option_title')],
            $storeOptionJoin,
            ['title' => $titleCheckSql]
        )->where(
            'cpe.entity_id = ?',
            $productId
        );

        if ($titles = $connection->fetchCol($select)) {
            $searchData = array_merge($searchData, $titles);
        }

        //select option type titles
        $defaultOptionJoin = implode(
            ' AND ',
            [
                'option_title_default.option_type_id=option_type.option_type_id',
                $connection->quoteInto(
                    'option_title_default.store_id = ?',
                    \Magento\Store\Model\Store::DEFAULT_STORE_ID
                )
            ]
        );

        $storeOptionJoin = implode(
            ' AND ',
            [
                'option_title_store.option_type_id = option_type.option_type_id',
                $connection->quoteInto('option_title_store.store_id = ?', (int)$storeId)
            ]
        );

        $select = $connection->select()->from(
            ['product_option' => $this->getMainTable()],
            null
        )->join(
            ['option_type' => $this->getTable('catalog_product_option_type_value')],
            'option_type.option_id=product_option.option_id',
            []
        )->join(
            ['cpe' => $this->getTable('catalog_product_entity')],
            sprintf(
                'cpe.%s = product_option.product_id',
                $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField()
            ),
            []
        )->join(
            ['option_title_default' => $this->getTable('catalog_product_option_type_title')],
            $defaultOptionJoin,
            []
        )->joinLeft(
            ['option_title_store' => $this->getTable('catalog_product_option_type_title')],
            $storeOptionJoin,
            ['title' => $titleCheckSql]
        )->where(
            'cpe.entity_id = ?',
            $productId
        );

        if ($titles = $connection->fetchCol($select)) {
            $searchData = array_merge($searchData, $titles);
        }

        return $searchData;
    }

    /**
     * @return \Magento\Framework\EntityManager\MetadataPool
     */
    private function getMetadataPool()
    {
        if (null === $this->metadataPool) {
            $this->metadataPool = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\EntityManager\MetadataPool::class);
        }
        return $this->metadataPool;
    }
}
