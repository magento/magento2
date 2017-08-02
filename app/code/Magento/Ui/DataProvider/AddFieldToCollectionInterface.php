<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\DataProvider;

use Magento\Framework\Data\Collection;

/**
 * AddFieldToCollection interface
 * @since 2.0.0
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
     * @since 2.0.0
     */
    public function addField(Collection $collection, $field, $alias = null);
}
