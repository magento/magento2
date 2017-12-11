<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryImportExport\Model\Export;

use Magento\Framework\Exception\LocalizedException;
use Magento\Inventory\Model\ResourceModel\SourceItem\Collection;
use Magento\Framework\Data\Collection as AttributeCollection;

/**
 * @api
 */
interface SourceItemCollectionFactoryInterface
{
    /**
     * SourceItemCollection is used to gather all the data (with filters applied) which need to be exported
     *
     * @param AttributeCollection $attributeCollection
     * @param array $filters
     * @return Collection
     * @throws LocalizedException
     */
    public function create(AttributeCollection $attributeCollection, array $filters): Collection;
}
