<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Model\ResourceModel\Export;

use Magento\Framework\Data\Collection;

/**
 * Class AttributeGridCollection
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
        $value = (string)$condition['like'];
        $value = trim(trim($value, "'"), "%");
        foreach ($this->getItems() as $item) {
            if (stripos($item->getData($field), $value) === false) {
                $this->removeItemByKey($item->getId());
            }
        }

        return $this;
    }

    /**
     * Add select order
     *
     * @param  string $field
     * @param  string $direction
     * @return $this
     */
    public function setOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        $this->_orderField = $field;
        uasort($this->_items, [$this, 'compareAttributes']);

        if ($direction == self::SORT_ORDER_DESC) {
            $this->_items = array_reverse($this->_items, true);
        }

        return $this;
    }

    /**
     * Compare two collection items
     *
     * @param \Magento\Framework\DataObject $a
     * @param \Magento\Framework\DataObject $b
     * @return int
     */
    public function compareAttributes(\Magento\Framework\DataObject $a, \Magento\Framework\DataObject $b)
    {
        return strnatcmp($a->getData($this->_orderField), $b->getData($this->_orderField));
    }
}
