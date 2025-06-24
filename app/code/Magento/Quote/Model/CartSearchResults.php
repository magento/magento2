<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Quote\Api\Data\CartSearchResultsInterface;

/**
 * Service Data Object with Cart search results.
 */
class CartSearchResults extends AbstractSimpleObject implements CartSearchResultsInterface
{
    /**
     * @inheritdoc
     */
    public function setItems(?array $items = null)
    {
        return $this->setData(self::KEY_ITEMS, $items);
    }

    /**
     * @inheritdoc
     */
    public function getItems()
    {
        return $this->_get(self::KEY_ITEMS) === null ? [] : $this->_get(self::KEY_ITEMS);
    }

    /**
     * @inheritdoc
     */
    public function getSearchCriteria()
    {
        return $this->_get(self::KEY_SEARCH_CRITERIA);
    }

    /**
     * @inheritdoc
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        return $this->setData(self::KEY_SEARCH_CRITERIA, $searchCriteria);
    }

    /**
     * @inheritdoc
     */
    public function getTotalCount()
    {
        return $this->_get(self::KEY_TOTAL_COUNT);
    }

    /**
     * @inheritdoc
     */
    public function setTotalCount($count)
    {
        return $this->setData(self::KEY_TOTAL_COUNT, $count);
    }
}
