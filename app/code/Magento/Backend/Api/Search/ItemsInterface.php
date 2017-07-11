<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Api\Search;

/**
 * @api
 */
interface ItemsInterface
{
    const LIMIT = 'limit';
    const START = 'start';
    const QUERY = 'query';

    /**
     * get the search result items
     *
     * @return array
     */
    public function getResults();

    /**
     * set offset
     *
     * @param int $start
     * @return ItemsInterface
     */
    public function setStart($start);

    /**
     * set search query
     *
     * @param string $query
     * @return ItemsInterface
     */
    public function setQuery($query);

    /**
     * set limit
     *
     * @param int $limit
     * @return ItemsInterface
     */
    public function setLimit($limit);
}
