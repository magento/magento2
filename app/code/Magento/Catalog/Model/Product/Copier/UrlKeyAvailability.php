<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Copier;

use Magento\Catalog\Model\Product;

/**
 * Checks whether the URL key currently set on a product duplicate can be used in the duplicate's store scope.
 */
class UrlKeyAvailability
{
    /**
     * Check that no other product uses the duplicate's URL key
     *
     * @param Product $duplicate
     * @return bool
     */
    public function isAvailable(Product $duplicate): bool
    {
        $attribute = $duplicate->getResource()->getAttribute('url_key');

        return (bool)$attribute->getEntity()->checkAttributeUniqueValue($attribute, $duplicate);
    }
}
