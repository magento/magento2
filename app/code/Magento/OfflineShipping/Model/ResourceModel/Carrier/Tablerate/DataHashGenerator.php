<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate;

/**
 * Class \Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\DataHashGenerator
 *
 * @since 2.1.0
 */
class DataHashGenerator
{
    /**
     * @param array $data
     * @return string
     * @since 2.1.0
     */
    public function getHash(array $data)
    {
        $countryId = $data['dest_country_id'];
        $regionId = $data['dest_region_id'];
        $zipCode = $data['dest_zip'];
        $conditionValue = $data['condition_value'];

        return sprintf("%s-%d-%s-%F", $countryId, $regionId, $zipCode, $conditionValue);
    }
}
