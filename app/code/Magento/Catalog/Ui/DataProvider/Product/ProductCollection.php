<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\DataProvider\Product;

use Magento\Framework\DB\Select;

/**
 * Collection which is used for rendering product list in the backend.
 *
 * Used for product grid and customizes behavior of the default Product collection for grid needs.
 */
class ProductCollection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    /**
     * Disables using of price index for grid rendering
     *
     * Admin area shouldn't use price index and should rely on actual product data instead.
     *
     * @codeCoverageIgnore
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function _productLimitationJoinPrice()
    {
        $this->_productLimitationFilters->setUsePriceIndex(false);
        return $this->_productLimitationPrice(true);
    }

    /**
     * @inheritdoc
     */
    public function getSize()
    {
        if ($this->_scopeConfig->getValue('admin/grid/show_approximate_total_number_of_products')) {
            $sql = $this->getSelectCountSql();
            return $this->analyzeCount($sql);
        }

        return parent::getSize();
    }

    /**
     * Analyze amount of entities in DB.
     *
     * @param $sql
     * @return int|mixed
     * @throws \Zend_Db_Select_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    private function analyzeCount($sql)
    {
        $results = $this->getConnection()->query('EXPLAIN ' . $sql)->fetchAll();
        $alias = $this->getMainTableAlias();

        foreach ($results as $result) {
            if ($result['table'] == $alias) {
                return $result['rows'];
            }
        }

        return 0;
    }

    /**
     * Identify main table alias or its name if alias is not defined.
     *
     * @return string
     * @throws \LogicException
     * @throws \Zend_Db_Select_Exception
     */
    private function getMainTableAlias()
    {
        foreach ($this->getSelect()->getPart(Select::FROM) as $tableAlias => $tableMetadata) {
            if ($tableMetadata['joinType'] == 'from') {
                return $tableAlias;
            }
        }
        throw new \LogicException("Main table cannot be identified.");
    }
}
