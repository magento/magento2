<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\ResourceModel\Advanced;

use Magento\Framework\DB\Select;

/**
 * Advanced search collection.
 */
class Collection extends \Magento\CatalogSearch\Model\ResourceModel\Advanced\Collection
{
    /**
     * Set Order field
     *
     * @param string $attribute
     * @param string $dir
     * @return $this
     */
    public function setOrder($attribute, $dir = Select::SQL_DESC)
    {
        if (is_array($attribute)) {
            parent::setOrder($attribute, $dir);
        }
        parent::addOrder($attribute, $dir);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addCategoryFilter(\Magento\Catalog\Model\Category $category)
    {
        $this->addFieldToFilter('category_ids', $category->getId());
        $this->_productLimitationPrice();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setVisibility($visibility)
    {
        $this->addFieldToFilter('visibility', $visibility);
        return $this;
    }
}
