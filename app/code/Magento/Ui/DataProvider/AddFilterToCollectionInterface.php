<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\DataProvider;

use Magento\Framework\Data\Collection;

/**
 * AddFilterToCollection interface
 * @since 2.0.0
 */
interface AddFilterToCollectionInterface
{
    /**
     * @param Collection $collection
     * @param string $field
     * @param string|null $condition
     * @return void
     * @since 2.0.0
     */
    public function addFilter(Collection $collection, $field, $condition = null);
}
