<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Layer;

use Magento\Catalog\Model\Layer\Filter\Item;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\DataObject;

/**
 * Layered navigation state model
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class State extends DataObject
{
    /**
     * Add filter item to layer state
     *
     * @param   Item $filter
     * @return  $this
     */
    public function addFilter($filter)
    {
        $filters = $this->getFilters();
        $filters[] = $filter;
        $this->setFilters($filters);
        return $this;
    }

    /**
     * Set layer state filter items
     *
     * @param  Item[] $filters
     * @return $this
     * @throws LocalizedException
     */
    public function setFilters($filters)
    {
        if (!is_array($filters)) {
            throw new LocalizedException(__('The filters must be an array.'));
        }
        $this->setData('filters', $filters);
        return $this;
    }

    /**
     * Get applied to layer filter items
     *
     * @return Item[]
     */
    public function getFilters()
    {
        $filters = $this->getData('filters');
        if ($filters === null) {
            $filters = [];
            $this->setData('filters', $filters);
        }
        return $filters;
    }
}
