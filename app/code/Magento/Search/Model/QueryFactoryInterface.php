<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Model;

interface QueryFactoryInterface
{
    /**
     * @return QueryInterface
     */
    public function get();
}
