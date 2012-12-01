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
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Catalog product option values collection
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Resource_Product_Option_Value_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('Mage_Catalog_Model_Product_Option_Value', 'Mage_Catalog_Model_Resource_Product_Option_Value');
    }

    /**
     * Add price, title to result
     *
     * @param int $storeId
     * @return Mage_Catalog_Model_Resource_Product_Option_Value_Collection
     */
    public function getValues($storeId)
    {
        $this->addPriceToResult($storeId)
             ->addTitleToResult($storeId);

        return $this;
    }

    /**
     * Add titles to result
     *
     * @param int $storeId
     * @return Mage_Catalog_Model_Resource_Product_Option_Value_Collection
     */
    public function addTitlesToResult($storeId)
    {
        $adapter = $this->getConnection();
        $optionTypePriceTable = $this->getTable('catalog_product_option_type_price');
        $optionTitleTable     = $this->getTable('catalog_product_option_type_title');
        $priceExpr = $adapter->getCheckSql(
            'store_value_price.price IS NULL',
            'default_value_price.price',
            'store_value_price.price'
        );
        $priceTypeExpr = $adapter->getCheckSql(
            'store_value_price.price_type IS NULL',
            'default_value_price.price_type',
            'store_value_price.price_type'
        );
        $titleExpr = $adapter->getCheckSql(
            'store_value_title.title IS NULL',
            'default_value_title.title',
            'store_value_title.title'
        );
        $joinExprDefaultPrice = 'default_value_price.option_type_id = main_table.option_type_id AND '
                  . $adapter->quoteInto('default_value_price.store_id = ?', Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);

        $joinExprStorePrice = 'store_value_price.option_type_id = main_table.option_type_id AND '
                       . $adapter->quoteInto('store_value_price.store_id = ?', $storeId);

        $joinExprTitle = 'store_value_title.option_type_id = main_table.option_type_id AND '
                       . $adapter->quoteInto('store_value_title.store_id = ?', $storeId);

        $this->getSelect()
            ->joinLeft(
                array('default_value_price' => $optionTypePriceTable),
                $joinExprDefaultPrice,
                array('default_price'=>'price','default_price_type'=>'price_type')
            )
            ->joinLeft(
                array('store_value_price' => $optionTypePriceTable),
                $joinExprStorePrice,
                array(
                    'store_price'       => 'price',
                    'store_price_type'  => 'price_type',
                    'price'             => $priceExpr,
                    'price_type'        => $priceTypeExpr
                )
            )
            ->join(
                array('default_value_title' => $optionTitleTable),
                'default_value_title.option_type_id = main_table.option_type_id',
                array('default_title' => 'title')
            )
            ->joinLeft(
                array('store_value_title' => $optionTitleTable),
                $joinExprTitle,
                array(
                    'store_title' => 'title',
                    'title'       => $titleExpr)
            )
            ->where('default_value_title.store_id = ?', Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);

        return $this;
    }

    /**
     * Add title result
     *
     * @param int $storeId
     * @return Mage_Catalog_Model_Resource_Product_Option_Value_Collection
     */
    public function addTitleToResult($storeId)
    {
        $optionTitleTable = $this->getTable('catalog_product_option_type_title');
        $titleExpr = $this->getConnection()
            ->getCheckSql('store_value_title.title IS NULL', 'default_value_title.title', 'store_value_title.title');

        $joinExpr = 'store_value_title.option_type_id = main_table.option_type_id AND '
                  . $this->getConnection()->quoteInto('store_value_title.store_id = ?', $storeId);
        $this->getSelect()
            ->join(
                array('default_value_title' => $optionTitleTable),
                'default_value_title.option_type_id = main_table.option_type_id',
                array('default_title' => 'title')
            )
            ->joinLeft(
                array('store_value_title' => $optionTitleTable),
                $joinExpr,
                array(
                    'store_title'   => 'title',
                    'title'         => $titleExpr
                )
            )
            ->where('default_value_title.store_id = ?', Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);

        return $this;
    }

    /**
     * Add price to result
     *
     * @param int $storeId
     * @return Mage_Catalog_Model_Resource_Product_Option_Value_Collection
     */
    public function addPriceToResult($storeId)
    {
        $optionTypeTable = $this->getTable('catalog_product_option_type_price');
        $priceExpr = $this->getConnection()
            ->getCheckSql('store_value_price.price IS NULL', 'default_value_price.price', 'store_value_price.price');
        $priceTypeExpr = $this->getConnection()
            ->getCheckSql(
                'store_value_price.price_type IS NULL',
                'default_value_price.price_type',
                'store_value_price.price_type'
            );

        $joinExprDefault = 'default_value_price.option_type_id = main_table.option_type_id AND '
                        . $this->getConnection()->quoteInto('default_value_price.store_id = ?', Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);
        $joinExprStore = 'store_value_price.option_type_id = main_table.option_type_id AND '
                       . $this->getConnection()->quoteInto('store_value_price.store_id = ?', $storeId);
        $this->getSelect()
            ->joinLeft(
                array('default_value_price' => $optionTypeTable),
                $joinExprDefault,
                array(
                    'default_price' => 'price',
                    'default_price_type'=>'price_type'
                )
            )
            ->joinLeft(
                array('store_value_price' => $optionTypeTable),
                $joinExprStore,
                array(
                    'store_price'       => 'price',
                    'store_price_type'  => 'price_type',
                    'price'             => $priceExpr,
                    'price_type'        => $priceTypeExpr
                )
            );

        return $this;
    }

    /**
     * Add option filter
     *
     * @param array $optionIds
     * @param int $storeId
     * @return Mage_Catalog_Model_Resource_Product_Option_Value_Collection
     */
    public function getValuesByOption($optionIds, $storeId = null)
    {
        if (!is_array($optionIds)) {
            $optionIds = array($optionIds);
        }

        return $this->addFieldToFilter('main_table.option_type_id', array('in' => $optionIds));
    }

    /**
     * Add option to filter
     *
     * @param array|Mage_Catalog_Model_Product_Option|int $option
     * @return Mage_Catalog_Model_Resource_Product_Option_Value_Collection
     */
    public function addOptionToFilter($option)
    {
        if (empty($option)) {
            $this->addFieldToFilter('option_id', '');
        } elseif (is_array($option)) {
            $this->addFieldToFilter('option_id', array('in' => $option));
        } elseif ($option instanceof Mage_Catalog_Model_Product_Option) {
            $this->addFieldToFilter('option_id', $option->getId());
        } else {
            $this->addFieldToFilter('option_id', $option);
        }

        return $this;
    }
}
