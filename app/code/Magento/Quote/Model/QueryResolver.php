<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model;

class QueryResolver
{
    /**
     * @var bool
     */
    protected $singleQuery;

    /**
     * @param bool $singleQuery
     */
    public function __construct(
        $singleQuery = true
    ) {
        $this->singleQuery = $singleQuery;
    }

    /**
     * Get flag value
     *
     * @return bool
     */
    public function isSingleQuery()
    {
        return $this->singleQuery;
    }
}
