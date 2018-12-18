<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Model\ResourceModel\Fulltext;

use Magento\Framework\DB\Select;

/**
 * Fulltext Collection for elasticsearch.
 *
 * @api
 */
class Collection extends \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection
{
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
}
