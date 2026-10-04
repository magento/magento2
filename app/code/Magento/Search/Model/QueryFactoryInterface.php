<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Search\Model;

/**
 * @deprecated 100.2.0
 */
interface QueryFactoryInterface
{
    /**
     * @return QueryInterface
     */
    public function get();
}
