<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryAdminUi\Model\Source;

/**
 * Prepare region data. Specified for form structure
 */
class SourceRegionDataProcessor
{
    /**
     * Processes source region data
     *
     * @param array $data
     * @return array
     */
    public function execute(array $data): array
    {
        if (!isset($data['region_id']) || '' === $data['region_id']) {
            $data['region_id'] = null;
        }

        if (null === $data['region_id'] || !isset($data['region']) || '' === trim($data['region'])) {
            $data['region'] = null;
        }

        return $data;
    }
}
