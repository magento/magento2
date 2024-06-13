<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Ui\DataProvider;

use Magento\Framework\Data\Collection;

/**
 * AddFilterToCollection interface
 *
 * @api
 */
interface AddFilterToCollectionInterface
{
    /**
     * @param Collection $collection
     * @param string $field
     * @param string|null $condition
     * @return void
     */
    public function addFilter(Collection $collection, $field, $condition = null);
}
