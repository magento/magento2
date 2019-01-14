<?php

namespace Magento\Catalog\Model\Product\UrlConfig;

interface UrlConfigInterface
{
    /**
     * @param int $storeId
     *
     * @return bool
     */
    public function useCategoryInUrl($storeId);
}
