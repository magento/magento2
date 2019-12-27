<?php
namespace Smetana\Third\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Partner GetList Interface
 *
 * @package Smetana\Third\Api\Data
 */
interface PartnerSearchResultInterface extends SearchResultsInterface
{
    /**
     * Get items list.
     *
     * @return PartnerInterface[]
     */
    public function getItems();

    /**
     * Set items list.
     *
     * @param PartnerInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
