<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Model\Search;

use Magento\Framework\Data\Collection;

/**
 * Search collection provider.
 *
 * @api
 */
interface ItemCollectionProviderInterface
{
    /**
     * Get collection.
     *
     * @return Collection
     */
    public function getCollection() : Collection;
}
