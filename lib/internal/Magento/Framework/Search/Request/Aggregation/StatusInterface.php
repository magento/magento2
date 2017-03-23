<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Request\Aggregation;

interface StatusInterface
{
    /**
     * @return bool
     */
    public function isEnabled();
}
