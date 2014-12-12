<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Model\Layer\Category;

use Magento\Catalog\Model\Layer\AvailabilityFlagInterface;

class AvailabilityFlag implements AvailabilityFlagInterface
{
    /**
     * Is filter enabled
     *
     * @param \Magento\Catalog\Model\Layer $layer
     * @param array $filters
     * @return bool
     */
    public function isEnabled($layer, array $filters = [])
    {
        return $this->canShowOptions($filters) || count($layer->getState()->getFilters());
    }

    /**
     * @param array $filters
     * @return bool
     */
    protected function canShowOptions($filters)
    {
        foreach ($filters as $filter) {
            if ($filter->getItemsCount()) {
                return true;
            }
        }

        return false;
    }
}
