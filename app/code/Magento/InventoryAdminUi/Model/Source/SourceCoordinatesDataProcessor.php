<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryAdminUi\Model\Source;

/**
 * Prepare source coordinates data (latitude and longitude). Specified for form structure
 */
class SourceCoordinatesDataProcessor
{
    /**
     * @param array $data
     * @return array
     */
    public function execute(array $data): array
    {
        if (!isset($data['latitude']) || '' === $data['latitude']) {
            $data['latitude'] = null;
        }

        if (!isset($data['longitude']) || '' === $data['longitude']) {
            $data['longitude'] = null;
        }

        return $data;
    }
}
