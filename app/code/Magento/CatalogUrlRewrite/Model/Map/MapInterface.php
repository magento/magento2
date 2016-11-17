<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Map;

use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Interface for client map
 */
interface MapInterface
{
    /**
     * Gets the results from a map by identifiers
     *
     * @param array $identifiers
     * @return UrlRewrite[]
     */
    public function getByIdentifiers($identifiers);
}
