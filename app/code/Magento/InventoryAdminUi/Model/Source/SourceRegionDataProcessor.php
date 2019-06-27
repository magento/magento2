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
        if ($this->doesFieldEmpty('region_id', $data)) {
            $data['region_id'] = null;
        }

        if ($this->doesFieldEmpty('region', $data)) {
            $data['region'] = null;
        }

        return $data;
    }

    /**
     * Checks whether field has post value and this value doesn't empty
     *
     * @param string $fieldName
     * @param array $data
     *
     * @return bool
     */
    private function doesFieldEmpty(string $fieldName, array $data): bool
    {
        return !isset($data[$fieldName]) || '' === $data[$fieldName];
    }
}
