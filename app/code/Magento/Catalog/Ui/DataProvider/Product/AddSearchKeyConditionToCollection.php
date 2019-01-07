<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Ui\DataProvider\Product;

use Magento\Ui\DataProvider\AddFilterToCollectionInterface;
use Magento\Framework\Data\Collection;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Class AddSearchKeyConditionToCollection
 */
class AddSearchKeyConditionToCollection implements AddFilterToCollectionInterface
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addFilter(Collection $collection, $field, $condition = null) : void
    {
        if (isset($condition['fulltext']) && !empty($condition['fulltext'])) {
            $collection->addFieldToFilter(
                ProductInterface::NAME,
                $condition['fulltext']
            )->addFieldToFilter(
                ProductInterface::SKU,
                $condition['fulltext']
            );
        }
    }
}
