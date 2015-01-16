<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Resource\Product\Option;

/**
 * Catalog product custom option resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Value extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
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
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $config
    ) {
        $this->_currencyFactory = $currencyFactory;
        $this->_storeManager = $storeManager;
        $this->_config = $config;
        parent::__construct($resource);
    }

    /**
     * Define main table and initialize connection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_product_option_type_value', 'option_type_id');
    }

    /**
     * Proceed operations after object is saved
     * Save options store data
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\Model\Resource\Db\AbstractDb
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->_saveValuePrices($object);
        $this->_saveValueTitles($object);

        return parent::_afterSave($object);
    }

    /**
     * Save option value price data
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return void
     */
    protected function _saveValuePrices(\Magento\Framework\Model\AbstractModel $object)
    {
        $priceTable = $this->getTable('catalog_product_option_type_price');

        $price = (double)sprintf('%F', $object->getPrice());
        $priceType = $object->getPriceType();

        if ($object->getPrice() && $priceType) {
            //save for store_id = 0
            $select = $this->_getReadAdapter()->select()->from(
                $priceTable,
                'option_type_id'
            )->where(
                'option_type_id = ?',
                (int)$object->getId()
            )->where(
                'store_id = ?',
                \Magento\Store\Model\Store::DEFAULT_STORE_ID
            );
            $optionTypeId = $this->_getReadAdapter()->fetchOne($select);

            if ($optionTypeId) {
                if ($object->getStoreId() == '0') {
                    $bind = ['price' => $price, 'price_type' => $priceType];
                    $where = [
                        'option_type_id = ?' => $optionTypeId,
                        'store_id = ?' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ];

                    $this->_getWriteAdapter()->update($priceTable, $bind, $where);
                }
            } else {
                $bind = [
                    'option_type_id' => (int)$object->getId(),
                    'store_id' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    'price' => $price,
                    'price_type' => $priceType,
                ];
                $this->_getWriteAdapter()->insert($priceTable, $bind);
            }
        }

        $scope = (int)$this->_config->getValue(
            \Magento\Store\Model\Store::XML_PATH_PRICE_SCOPE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($scope == \Magento\Store\Model\Store::PRICE_SCOPE_WEBSITE
            && $priceType
            && $object->getPrice()
            && $object->getStoreId() != \Magento\Store\Model\Store::DEFAULT_STORE_ID
        ) {
            $baseCurrency = $this->_config->getValue(
                \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                'default'
            );

            $storeIds = $this->_storeManager->getStore($object->getStoreId())->getWebsite()->getStoreIds();
            if (is_array($storeIds)) {
                foreach ($storeIds as $storeId) {
                    if ($priceType == 'fixed') {
                        $storeCurrency = $this->_storeManager->getStore($storeId)->getBaseCurrencyCode();
                        /** @var $currencyModel \Magento\Directory\Model\Currency */
                        $currencyModel = $this->_currencyFactory->create();
                        $currencyModel->load($baseCurrency);
                        $rate = $currencyModel->getRate($storeCurrency);
                        if (!$rate) {
                            $rate = 1;
                        }
                        $newPrice = $price * $rate;
                    } else {
                        $newPrice = $price;
                    }

                    $select = $this->_getReadAdapter()->select()->from(
                        $priceTable,
                        'option_type_id'
                    )->where(
                        'option_type_id = ?',
                        (int)$object->getId()
                    )->where(
                        'store_id = ?',
                        (int)$storeId
                    );
                    $optionTypeId = $this->_getReadAdapter()->fetchOne($select);

                    if ($optionTypeId) {
                        $bind = ['price' => $newPrice, 'price_type' => $priceType];
                        $where = ['option_type_id = ?' => (int)$optionTypeId, 'store_id = ?' => (int)$storeId];

                        $this->_getWriteAdapter()->update($priceTable, $bind, $where);
                    } else {
                        $bind = [
                            'option_type_id' => (int)$object->getId(),
                            'store_id' => (int)$storeId,
                            'price' => $newPrice,
                            'price_type' => $priceType,
                        ];

                        $this->_getWriteAdapter()->insert($priceTable, $bind);
                    }
                }
            }
        } else {
            if ($scope == \Magento\Store\Model\Store::PRICE_SCOPE_WEBSITE
                && !$object->getPrice()
                && !$priceType
            ) {
                $storeIds = $this->_storeManager->getStore($object->getStoreId())->getWebsite()->getStoreIds();
                foreach ($storeIds as $storeId) {
                    $where = [
                        'option_type_id = ?' => (int)$object->getId(),
                        'store_id = ?' => $storeId,
                    ];
                    $this->_getWriteAdapter()->delete($priceTable, $where);
                }
            }
        }
    }

    /**
     * Save option value title data
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return void
     */
    protected function _saveValueTitles(\Magento\Framework\Model\AbstractModel $object)
    {
        foreach ([\Magento\Store\Model\Store::DEFAULT_STORE_ID, $object->getStoreId()] as $storeId) {
            $titleTable = $this->getTable('catalog_product_option_type_title');
            $select = $this->_getReadAdapter()->select()->from(
                $titleTable,
                ['option_type_id']
            )->where(
                'option_type_id = ?',
                (int)$object->getId()
            )->where(
                'store_id = ?',
                (int)$storeId
            );
            $optionTypeId = $this->_getReadAdapter()->fetchOne($select);
            $existInCurrentStore = $this->getOptionIdFromOptionTable($titleTable, (int)$object->getId(), (int)$storeId);
            if ($object->getTitle()) {
                if ($existInCurrentStore) {
                    if ($storeId == $object->getStoreId()) {
                        $where = [
                            'option_type_id = ?' => (int)$optionTypeId,
                            'store_id = ?' => $storeId,
                        ];
                        $bind = ['title' => $object->getTitle()];
                        $this->_getWriteAdapter()->update($titleTable, $bind, $where);
                    }
                } else {
                    $existInDefaultStore = $this->getOptionIdFromOptionTable(
                        $titleTable,
                        (int)$object->getId(),
                        \Magento\Store\Model\Store::DEFAULT_STORE_ID
                    );
                    // we should insert record into not default store only of if it does not exist in default store
                    if (($storeId == \Magento\Store\Model\Store::DEFAULT_STORE_ID && !$existInDefaultStore)
                        || ($storeId != \Magento\Store\Model\Store::DEFAULT_STORE_ID && !$existInCurrentStore)
                    ) {
                        $bind = [
                            'option_type_id' => (int)$object->getId(),
                            'store_id' => $storeId,
                            'title' => $object->getTitle(),
                        ];
                        $this->_getWriteAdapter()->insert($titleTable, $bind);
                    }
                }
            } else {
                if ($storeId
                    && $optionTypeId
                    && $object->getStoreId() > \Magento\Store\Model\Store::DEFAULT_STORE_ID
                ) {
                    $where = [
                        'option_type_id = ?' => (int)$optionTypeId,
                        'store_id = ?' => $storeId,
                    ];
                    $this->_getWriteAdapter()->delete($titleTable, $where);
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
    protected function getOptionIdFromOptionTable($tableName, $optionId, $storeId)
    {
        $readAdapter = $this->_getReadAdapter();
        $select = $readAdapter->select()->from(
            $tableName,
            ['option_type_id']
        )->where(
            'option_type_id = ?',
            $optionId
        )->where(
            'store_id = ?',
            (int)$storeId
        );
        return $readAdapter->fetchOne($select);
    }

    /**
     * Delete values by option id
     *
     * @param int $optionId
     * @return $this
     */
    public function deleteValue($optionId)
    {
        $statement = $this->_getReadAdapter()->select()->from(
            $this->getTable('catalog_product_option_type_value')
        )->where(
            'option_id = ?',
            $optionId
        );

        $rowSet = $this->_getReadAdapter()->fetchAll($statement);

        foreach ($rowSet as $optionType) {
            $this->deleteValues($optionType['option_type_id']);
        }

        $this->_getWriteAdapter()->delete($this->getMainTable(), ['option_id = ?' => $optionId]);

        return $this;
    }

    /**
     * Delete values by option type
     *
     * @param int $optionTypeId
     * @return void
     */
    public function deleteValues($optionTypeId)
    {
        $condition = ['option_type_id = ?' => $optionTypeId];

        $this->_getWriteAdapter()->delete($this->getTable('catalog_product_option_type_price'), $condition);

        $this->_getWriteAdapter()->delete($this->getTable('catalog_product_option_type_title'), $condition);
    }

    /**
     * Duplicate product options value
     *
     * @param \Magento\Catalog\Model\Product\Option\Value $object
     * @param int $oldOptionId
     * @param int $newOptionId
     * @return \Magento\Catalog\Model\Product\Option\Value
     */
    public function duplicate(\Magento\Catalog\Model\Product\Option\Value $object, $oldOptionId, $newOptionId)
    {
        $writeAdapter = $this->_getWriteAdapter();
        $readAdapter = $this->_getReadAdapter();
        $select = $readAdapter->select()->from($this->getMainTable())->where('option_id = ?', $oldOptionId);
        $valueData = $readAdapter->fetchAll($select);

        $valueCond = [];

        foreach ($valueData as $data) {
            $optionTypeId = $data[$this->getIdFieldName()];
            unset($data[$this->getIdFieldName()]);
            $data['option_id'] = $newOptionId;

            $writeAdapter->insert($this->getMainTable(), $data);
            $valueCond[$optionTypeId] = $writeAdapter->lastInsertId($this->getMainTable());
        }

        unset($valueData);

        foreach ($valueCond as $oldTypeId => $newTypeId) {
            // price
            $priceTable = $this->getTable('catalog_product_option_type_price');
            $columns = [new \Zend_Db_Expr($newTypeId), 'store_id', 'price', 'price_type'];

            $select = $readAdapter->select()->from(
                $priceTable,
                []
            )->where(
                'option_type_id = ?',
                $oldTypeId
            )->columns(
                $columns
            );
            $insertSelect = $writeAdapter->insertFromSelect(
                $select,
                $priceTable,
                ['option_type_id', 'store_id', 'price', 'price_type']
            );
            $writeAdapter->query($insertSelect);

            // title
            $titleTable = $this->getTable('catalog_product_option_type_title');
            $columns = [new \Zend_Db_Expr($newTypeId), 'store_id', 'title'];

            $select = $this->_getReadAdapter()->select()->from(
                $titleTable,
                []
            )->where(
                'option_type_id = ?',
                $oldTypeId
            )->columns(
                $columns
            );
            $insertSelect = $writeAdapter->insertFromSelect(
                $select,
                $titleTable,
                ['option_type_id', 'store_id', 'title']
            );
            $writeAdapter->query($insertSelect);
        }

        return $object;
    }
}
