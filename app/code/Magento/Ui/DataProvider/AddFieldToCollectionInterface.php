<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Ui\DataProvider;

use Magento\Framework\Data\Collection;

/**
 * AddFieldToCollection interface
 *
 * @api
 */
interface AddFieldToCollectionInterface
{
    /**
     * Add field to collection reflection
     *
     * @param Collection $collection
     * @param string $field
     * @param string|null $alias
     * @return void
     */
    public function addField(Collection $collection, $field, $alias = null);
}
