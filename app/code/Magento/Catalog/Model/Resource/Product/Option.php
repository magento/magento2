<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Resource\Product;

/**
 * Catalog product custom option resource model
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Option extends \Magento\Core\Model\Resource\Db\AbstractDb
{
    /**
     * Store manager
     *
     * @var \Magento\Core\Model\StoreManagerInterface
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
     * @var \Magento\App\ConfigInterface
     */
    protected $_config;

    /**
     * Class constructor
     *
     * @param \Magento\App\Resource $resource
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\App\ConfigInterface $config
     */
    public function __construct(
        \Magento\App\Resource $resource,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\App\ConfigInterface $config
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
        $this->_init('catalog_product_option', 'option_id');
    }

    /**
     * Save options store data
     *
     * @param \Magento\Core\Model\AbstractModel $object
     * @return \Magento\Core\Model\Resource\Db\AbstractDb
     */
    protected function _afterSave(\Magento\Core\Model\AbstractModel $object)
    {
        $this->_saveValuePrices($object);
        $this->_saveValueTitles($object);

        return parent::_afterSave($object);
    }

    /**
     * Save value prices
     *
     * @param \Magento\Core\Model\AbstractModel $object
     * @return $this
     */
    protected function _saveValuePrices(\Magento\Core\Model\AbstractModel $object)
    {
        $priceTable   = $this->getTable('catalog_product_option_price');
        $readAdapter  = $this->_getReadAdapter();
        $writeAdapter = $this->_getWriteAdapter();

        /*
         * Better to check param 'price' and 'price_type' for saving.
         * If there is not price skip saving price
         */

        if ($object->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_FIELD
            || $object->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_AREA
            || $object->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_FILE
            || $object->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DATE
            || $object->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DATE_TIME
            || $object->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_TIME
        ) {
            //save for store_id = 0
            if (!$object->getData('scope', 'price')) {
                $statement = $readAdapter->select()
                    ->from($priceTable, 'option_id')
                    ->where('option_id = ?', $object->getId())
                    ->where('store_id = ?', \Magento\Core\Model\Store::DEFAULT_STORE_ID);
                $optionId = $readAdapter->fetchOne($statement);

                if ($optionId) {
                    if ($object->getStoreId() == '0') {
                        $data = $this->_prepareDataForTable(
                            new \Magento\Object(
                                array(
                                    'price'      => $object->getPrice(),
                                    'price_type' => $object->getPriceType())
                            ),
                            $priceTable
                        );

                        $writeAdapter->update(
                            $priceTable,
                            $data,
                            array(
                                'option_id = ?' => $object->getId(),
                                'store_id  = ?' => \Magento\Core\Model\Store::DEFAULT_STORE_ID,
                            )
                        );
                    }
                } else {
                    $data = $this->_prepareDataForTable(
                         new \Magento\Object(
                            array(
                                'option_id'  => $object->getId(),
                                'store_id'   => \Magento\Core\Model\Store::DEFAULT_STORE_ID,
                                'price'      => $object->getPrice(),
                                'price_type' => $object->getPriceType()
                            )
                        ),
                        $priceTable
                    );
                    $writeAdapter->insert($priceTable, $data);
                }
            }

            $scope = (int) $this->_storeManager->getStore()->getConfig(\Magento\Core\Model\Store::XML_PATH_PRICE_SCOPE);

            if ($object->getStoreId() != '0' && $scope == \Magento\Core\Model\Store::PRICE_SCOPE_WEBSITE) {

                $baseCurrency = $this->_config->getValue(\Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                    'default');

                $storeIds = $this->_storeManager->getStore($object->getStoreId())->getWebsite()->getStoreIds();
                if (is_array($storeIds)) {
                    foreach ($storeIds as $storeId) {
                        if ($object->getPriceType() == 'fixed') {
                            $storeCurrency = $this->_storeManager->getStore($storeId)->getBaseCurrencyCode();
                            $rate = $this->_currencyFactory->create()->load($baseCurrency)->getRate($storeCurrency);
                            if (!$rate) {
                                $rate=1;
                            }
                            $newPrice = $object->getPrice() * $rate;
                        } else {
                            $newPrice = $object->getPrice();
                        }

                        $statement = $readAdapter->select()
                            ->from($priceTable)
                            ->where('option_id = ?', $object->getId())
                            ->where('store_id  = ?', $storeId);

                        if ($readAdapter->fetchOne($statement)) {
                            $data = $this->_prepareDataForTable(
                                new \Magento\Object(
                                    array(
                                        'price'      => $newPrice,
                                        'price_type' => $object->getPriceType()
                                    )
                                ),
                                $priceTable
                            );

                            $writeAdapter->update(
                                $priceTable,
                                $data,
                                array(
                                    'option_id = ?' => $object->getId(),
                                    'store_id  = ?' => $storeId
                                )
                            );
                        } else {
                            $data = $this->_prepareDataForTable(
                                new \Magento\Object(
                                    array(
                                        'option_id'  => $object->getId(),
                                        'store_id'   => $storeId,
                                        'price'      => $newPrice,
                                        'price_type' => $object->getPriceType()
                                    )
                                ),
                                $priceTable
                            );
                            $writeAdapter->insert($priceTable, $data);
                        }
                    }// end foreach()
                }
            } elseif ($scope == \Magento\Core\Model\Store::PRICE_SCOPE_WEBSITE && $object->getData('scope', 'price')) {
                $writeAdapter->delete(
                    $priceTable,
                    array(
                        'option_id = ?' => $object->getId(),
                        'store_id  = ?' => $object->getStoreId()
                    )
                );
            }
        }

        return $this;
    }

    /**
     * Save titles
     *
     * @param \Magento\Core\Model\AbstractModel $object
     * @return void
     */
    protected function _saveValueTitles(\Magento\Core\Model\AbstractModel $object)
    {
        $readAdapter  = $this->_getReadAdapter();
        $writeAdapter = $this->_getWriteAdapter();
        $titleTable = $this->getTable('catalog_product_option_title');

        //title
        if (!$object->getData('scope', 'title')) {
            $statement = $readAdapter->select()
                ->from($titleTable)
                ->where('option_id = ?', $object->getId())
                ->where('store_id  = ?', \Magento\Core\Model\Store::DEFAULT_STORE_ID);

            if ($readAdapter->fetchOne($statement)) {
                if ($object->getStoreId() == '0') {
                    $data = $this->_prepareDataForTable(
                        new \Magento\Object(
                            array(
                                'title' => $object->getTitle()
                            )
                        ),
                        $titleTable
                    );

                    $writeAdapter->update(
                        $titleTable,
                        $data,
                        array(
                            'option_id = ?' => $object->getId(),
                            'store_id  = ?' => \Magento\Core\Model\Store::DEFAULT_STORE_ID
                        )
                    );
                }
            } else {
                $data = $this->_prepareDataForTable(
                    new \Magento\Object(
                        array(
                            'option_id' => $object->getId(),
                            'store_id'  => \Magento\Core\Model\Store::DEFAULT_STORE_ID,
                            'title'     => $object->getTitle()
                        )
                    ),
                    $titleTable
                );

                $writeAdapter->insert($titleTable, $data);
            }
        }

        if ($object->getStoreId() != '0' && !$object->getData('scope', 'title')) {
            $statement = $readAdapter->select()
                ->from($titleTable)
                ->where('option_id = ?', $object->getId())
                ->where('store_id  = ?', $object->getStoreId());

            if ($readAdapter->fetchOne($statement)) {
                $data = $this->_prepareDataForTable(
                    new \Magento\Object(
                        array(
                            'title' => $object->getTitle()
                        )
                    ),
                    $titleTable
                );

                $writeAdapter->update(
                    $titleTable,
                    $data,
                    array(
                        'option_id = ?' => $object->getId(),
                        'store_id  = ?' => $object->getStoreId()
                    )
                );
            } else {
                $data = $this->_prepareDataForTable(
                    new \Magento\Object(
                        array(
                            'option_id' => $object->getId(),
                            'store_id'  => $object->getStoreId(),
                            'title'     => $object->getTitle()
                        )
                    ),
                    $titleTable
                );
                $writeAdapter->insert($titleTable, $data);
            }
        } elseif ($object->getData('scope', 'title')) {
            $writeAdapter->delete(
                $titleTable,
                array(
                    'option_id = ?' => $object->getId(),
                    'store_id  = ?' => $object->getStoreId()
                )
            );
        }
    }

    /**
     * Delete prices
     *
     * @param int $optionId
     * @return $this
     */
    public function deletePrices($optionId)
    {
        $this->_getWriteAdapter()->delete(
            $this->getTable('catalog_product_option_price'),
            array(
                'option_id = ?' => $optionId
            )
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
        $this->_getWriteAdapter()->delete(
            $this->getTable('catalog_product_option_title'),
            array(
                'option_id = ?' => $optionId
            )
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
        $write  = $this->_getWriteAdapter();
        $read   = $this->_getReadAdapter();

        $optionsCond = array();
        $optionsData = array();

        // read and prepare original product options
        $select = $read->select()
            ->from($this->getTable('catalog_product_option'))
            ->where('product_id = ?', $oldProductId);

        $query = $read->query($select);

        while ($row = $query->fetch()) {
            $optionsData[$row['option_id']] = $row;
            $optionsData[$row['option_id']]['product_id'] = $newProductId;
            unset($optionsData[$row['option_id']]['option_id']);
        }

        // insert options to duplicated product
        foreach ($optionsData as $oId => $data) {
            $write->insert($this->getMainTable(), $data);
            $optionsCond[$oId] = $write->lastInsertId($this->getMainTable());
        }

        // copy options prefs
        foreach ($optionsCond as $oldOptionId => $newOptionId) {
            // title
            $table = $this->getTable('catalog_product_option_title');

            $select = $this->_getReadAdapter()->select()
                ->from($table, array(new \Zend_Db_Expr($newOptionId), 'store_id', 'title'))
                ->where('option_id = ?', $oldOptionId);

            $insertSelect = $write->insertFromSelect(
                $select,
                $table,
                array('option_id', 'store_id', 'title'),
                \Magento\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
            );
            $write->query($insertSelect);

            // price
            $table = $this->getTable('catalog_product_option_price');

            $select = $read->select()
                ->from($table, array(new \Zend_Db_Expr($newOptionId), 'store_id', 'price', 'price_type'))
                ->where('option_id = ?', $oldOptionId);

            $insertSelect = $write->insertFromSelect(
                $select, $table,
                array(
                    'option_id',
                    'store_id',
                    'price',
                    'price_type'
                ),
                \Magento\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
            );
            $write->query($insertSelect);

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
     */
    public function getSearchableData($productId, $storeId)
    {
        $searchData = array();

        $adapter = $this->_getReadAdapter();

        $titleCheckSql = $adapter->getCheckSql(
            'option_title_store.title IS NULL',
            'option_title_default.title',
            'option_title_store.title'
        );


        // retrieve options title

        $defaultOptionJoin = implode(
            ' AND ',
            array('option_title_default.option_id=product_option.option_id',
            $adapter->quoteInto('option_title_default.store_id = ?', \Magento\Core\Model\Store::DEFAULT_STORE_ID))
        );

        $storeOptionJoin = implode(
            ' AND ',
            array(
                'option_title_store.option_id=product_option.option_id',
                $adapter->quoteInto('option_title_store.store_id = ?', (int) $storeId))
        );

        $select = $adapter->select()
            ->from(array('product_option' => $this->getMainTable()), null)
            ->join(
                array('option_title_default' => $this->getTable('catalog_product_option_title')),
                $defaultOptionJoin,
                array()
            )
            ->joinLeft(
                array('option_title_store' => $this->getTable('catalog_product_option_title')),
                $storeOptionJoin,
                array('title' => $titleCheckSql)
            )
            ->where('product_option.product_id = ?', $productId);

        if ($titles = $adapter->fetchCol($select)) {
            $searchData = array_merge($searchData, $titles);
        }

        //select option type titles

        $defaultOptionJoin = implode(
            ' AND ', array(
                'option_title_default.option_type_id=option_type.option_type_id',
                $adapter->quoteInto('option_title_default.store_id = ?', \Magento\Core\Model\Store::DEFAULT_STORE_ID))
        );

        $storeOptionJoin = implode(
            ' AND ', array(
                'option_title_store.option_type_id = option_type.option_type_id',
                 $adapter->quoteInto('option_title_store.store_id = ?', (int) $storeId))
        );

        $select = $adapter->select()
            ->from(array('product_option' => $this->getMainTable()), null)
            ->join(
                array('option_type' => $this->getTable('catalog_product_option_type_value')),
                'option_type.option_id=product_option.option_id',
                array()
            )
            ->join(
                array('option_title_default' => $this->getTable('catalog_product_option_type_title')),
                $defaultOptionJoin,
                array()
            )
            ->joinLeft(
                array('option_title_store' => $this->getTable('catalog_product_option_type_title')),
                $storeOptionJoin,
                array('title' => $titleCheckSql)
            )
            ->where('product_option.product_id = ?', $productId);

        if ($titles = $adapter->fetchCol($select)) {
            $searchData = array_merge($searchData, $titles);
        }

        return $searchData;
    }
}
