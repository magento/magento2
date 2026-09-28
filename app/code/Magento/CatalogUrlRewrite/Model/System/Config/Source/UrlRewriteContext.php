<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Model\System\Config\Source;

use Magento\CatalogUrlRewrite\Model\ProductScopeRewriteGenerator;

class UrlRewriteContext implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Selectable options for URL rewrite context
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => ProductScopeRewriteGenerator::WEBSITE_URL_REWRITE_SCOPE, 'label' => __('Website')],
            ['value' => ProductScopeRewriteGenerator::STORE_VIEW_URL_REWRITE_SCOPE, 'label' => __('Store View')]
        ];
    }
}
