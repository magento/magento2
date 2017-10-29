<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryImportExport\Model\Export;

use Magento\Framework\Data\Collection as AttributeCollection;

/**
 * @api
 */
interface ColumnProviderInterface
{
    /**
     * @param AttributeCollection $attributeCollection
     * @param array $filters
     * @return array
     */
    public function getHeaders(AttributeCollection $attributeCollection, array $filters): array;

    /**
     * @param AttributeCollection $attributeCollection
     * @param array $filters
     * @return array
     */
    public function getColumns(AttributeCollection $attributeCollection, array $filters): array;
}
