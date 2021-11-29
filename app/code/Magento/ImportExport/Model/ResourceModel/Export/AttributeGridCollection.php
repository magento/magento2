<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Model\ResourceModel\Export;

use Magento\Framework\Data\Collection;
use Magento\Framework\Phrase;

/**
 * Association of attributes for grid
 */
class AttributeGridCollection extends Collection
{
    private const FILTERED_FLAG_NAME = 'agc_filtered';

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
     */
    public function getSize(): int
    {
        return count($this->getItems());
    }

    /**
     * @inheritDoc
     */
    public function addFieldToFilter($field, $condition)
    {
        if (isset($condition['like'])) {
            $value = $this->unescapeLikeValue((string)$condition['like']);
            $this->addFilter($field, $value);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function load($printQuery = false, $logQuery = false)
    {
        $this->filterCollection();
        $this->sortCollectionByAttributeCode();

        return $this;
    }

    /**
     * Add filters to collection
     *
     * @return $this
     */
    private function filterCollection()
    {
        if (!$this->getFlag(self::FILTERED_FLAG_NAME) && !empty($this->_filters)) {
            foreach ($this->_filters as $filter) {
                foreach ($this->_items as $item) {
                    $field = $item->getData($filter->getData('field')) ?? '';
                    if ($field instanceof Phrase) {
                        $field = (string)$field;
                    }
                    if (stripos($field, $filter->getData('value')) === false) {
                        $this->removeItemByKey($item->getId());
                    }
                }
            }
            $this->setFlag(self::FILTERED_FLAG_NAME, true);
        }

        return $this;
    }

    /**
     * Sort collection by attribute code
     *
     * @return $this
     */
    private function sortCollectionByAttributeCode()
    {
        $sortOrder = $this->_orders['attribute_code'];
        uasort($this->_items, function ($a, $b) use ($sortOrder) {
            $cmp = strnatcmp($a->getData('attribute_code'), $b->getData('attribute_code'));

            return $sortOrder === self::SORT_ORDER_ASC ? $cmp : -$cmp;
        });

        return $this;
    }

    /**
     * Unescape 'like' value from condition
     *
     * @param string $likeValue
     * @return string
     */
    private function unescapeLikeValue(string $likeValue): string
    {
        $replaceFrom = ['\\\\', '\_', '\%'];
        $replaceTo = ['\\', '_', '%'];
        $value = trim($likeValue, "'%");
        $value = str_replace($replaceFrom, $replaceTo, $value);

        return $value;
    }
}
