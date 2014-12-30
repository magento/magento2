<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Search\Request\Aggregation;

interface StatusInterface
{
    /**
     * @return bool
     */
    public function isEnabled();
}
