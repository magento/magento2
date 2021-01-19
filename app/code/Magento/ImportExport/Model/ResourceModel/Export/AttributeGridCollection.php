<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Model\ResourceModel\Export;

use Magento\Framework\Data\Collection;

/**
 * Association of attributes for grid
 */
class AttributeGridCollection extends Collection
{

    /**
     * Adding item to collection
     *
     * @param array $items
     * @return $this
     */
    public function setItems(array $items): self
    {
        foreach ($items as $item) {
            $this->addItem($item);
        }

        return $this;
    }

    /**
     * @inheritDoc
     *
     * @return int
     */
    public function getSize(): int
    {
        return count($this->getItems());
    }

    /**
     * @inheritDoc
     *
     * @param string $field
     * @param array|int|string $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition)
    {
        if (isset($condition['like'])) {
            $value = trim((string)$condition['like'], "'%");
            $this->addFilter($field, $value);
        }

        return $this;
    }

    /**
     * @inheritDoc
     *
     * @param false $printQuery
     * @param false $logQuery
     * @return $this
     */
    public function load($printQuery = false, $logQuery = false)
    {
        if (!$this->getFlag('isFilter') && !empty($this->_filters)) {
            foreach ($this->_filters as $filter) {
                foreach ($this->_items as $item) {
                    $field = $item->getData($filter->getData('field')) ?? '';
                    if (stripos($field, $filter->getData('value')) === false) {
                        $this->removeItemByKey($item->getId());
                    }
                }
                $this->setFlag('isFilter', true);
            }
        }

        $sortOrder = $this->_orders['attribute_code'];
        uasort($this->_items, function ($a, $b) use ($sortOrder) {
            $cmp = strnatcmp($a->getData('attribute_code'), $b->getData('attribute_code'));
            return $sortOrder === self::SORT_ORDER_ASC ? $cmp : -$cmp;
        });

        return $this;
    }
}
