<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\DataProvider;

use Magento\Framework\Data\Collection;

/**
 * AddFieldToCollection interface
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
