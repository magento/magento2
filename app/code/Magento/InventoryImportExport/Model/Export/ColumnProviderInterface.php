<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryImportExport\Model\Export;

use Magento\Framework\Data\Collection as AttributeCollection;

/**
 * @api
 */
interface ColumnProviderInterface
{
    /**
     * Returns header names for exported file
     *
     * @param AttributeCollection $attributeCollection
     * @param array $filters
     * @return array
     */
    public function getHeaders(AttributeCollection $attributeCollection, array $filters): array;

    /**
     * Returns column names for Collection Select
     *
     * @param AttributeCollection $attributeCollection
     * @param array $filters
     * @return array
     */
    public function getColumns(AttributeCollection $attributeCollection, array $filters): array;
}
