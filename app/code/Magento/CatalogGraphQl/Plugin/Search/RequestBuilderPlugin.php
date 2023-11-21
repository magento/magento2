<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogGraphQl\Plugin\Search;

use Magento\CatalogGraphQl\DataProvider\Product\RequestDataBuilder;
use Magento\Framework\Search\Request\Config;

class RequestBuilderPlugin
{
    public function __construct(private RequestDataBuilder $localData)
    {
    }

    /**
     * @param Config $subject
     * @param callable $proceed
     * @param string $requestName
     * @return array
     */
    public function aroundGet(Config $subject, callable $proceed, string $requestName) {

        if ($this->localData->getData($requestName)) {
            return $this->localData->getData($requestName);
        } else {
            return $proceed($requestName);
        }
    }
}
