<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Translation\Model\Js;

interface DataProviderInterface
{
    /**
     * Get translation data
     *
     * @return string[]
     */
    public function getData();
}
