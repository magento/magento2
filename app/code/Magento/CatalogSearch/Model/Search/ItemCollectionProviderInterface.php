<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search;

use Magento\Framework\Data\Collection;

/**
 * Search collection provider.
 */
interface ItemCollectionProviderInterface
{
    /**
     * @return Collection
     */
    public function getCollection() : Collection;
}
