<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Model;

/**
 * @deprecated 2.2.0
 */
interface QueryFactoryInterface
{
    /**
     * @return QueryInterface
     */
    public function get();
}
