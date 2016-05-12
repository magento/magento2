<?php
namespace Magento\Review\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface ReviewSearchResultsInterface
 * 
 * @api
 */
interface ReviewSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return \Magento\Review\Api\Data\ReviewInterface[]
     */
    public function getItems();

    /**
     * @param \Magento\Review\Model\Review[] $items
     * @return $this
     */
    public function setItems(array $items);
}