<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\DataProvider\Product;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\Exception\LocalizedException;
use Magento\Eav\Model\Entity\Attribute\AttributeInterface;

/**
 * Collection which is used for rendering product list in the backend.
 *
 * Used for product grid and customizes behavior of the default Product collection for grid needs.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
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
     * Add attribute filter to collection
     *
     * @param AttributeInterface|integer|string|array $attribute
     * @param null|string|array $condition
     * @param string $joinType
     * @return $this
     * @throws LocalizedException
     */
    public function addAttributeToFilter($attribute, $condition = null, $joinType = 'inner')
    {
        $storeId = (int)$this->getStoreId();
        if ($attribute === 'is_saleable'
            || is_array($attribute)
            || $storeId !== $this->getDefaultStoreId()
        ) {
            return parent::addAttributeToFilter($attribute, $condition, $joinType);
        }

        if ($attribute instanceof AttributeInterface) {
            $attributeModel = $attribute;
        } else {
            $attributeModel = $this->getEntity()->getAttribute($attribute);
            if ($attributeModel === false) {
                throw new LocalizedException(
                    __('Invalid attribute identifier for filter (%1)', get_class($attribute))
                );
            }
        }

        if ($attributeModel->isScopeGlobal() || $attributeModel->getBackend()->isStatic()) {
            return parent::addAttributeToFilter($attribute, $condition, $joinType);
        }

        $this->addAttributeToFilterAllStores($attributeModel, $condition);

        return $this;
    }

    /**
     * Add attribute to filter by all stores
     *
     * @param Attribute $attributeModel
     * @param array $condition
     * @return void
     */
    private function addAttributeToFilterAllStores(Attribute $attributeModel, array $condition): void
    {
        $tableName = $this->getTable($attributeModel->getBackendTable());
        $entity = $this->getEntity();
        $fKey = 'e.' . $this->getEntityPkName($entity);
        $pKey = $tableName . '.' . $this->getEntityPkName($entity);
        $condition = "({$pKey} = {$fKey}) AND ("
            . $this->_getConditionSql("{$tableName}.value", $condition)
            . ')';
        $selectExistsInAllStores = $this->getConnection()->select()->from($tableName);
        $this->getSelect()->exists($selectExistsInAllStores, $condition);
    }
}
