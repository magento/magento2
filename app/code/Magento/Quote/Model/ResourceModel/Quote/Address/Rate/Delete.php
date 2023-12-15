<?php
/************************************************************************
 *
 * Copyright 2023 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ***********************************************************************
 */
declare(strict_types=1);

namespace Magento\Quote\Model\ResourceModel\Quote\Address\Rate;

use Magento\Framework\App\ResourceConnection;
use Magento\Quote\Model\Quote\Address\Rate;

class Delete
{
    private const TABLE = 'quote_shipping_rate';
    private const FIELD_RATE_ID = 'rate_id';

    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Remove shipping rates
     *
     * @param Rate[] $rates
     * @return void
     */
    public function execute(array $rates): void
    {
        if (empty($rates)) {
            return;
        }
        $this->resourceConnection->getConnection()->delete(
            $this->resourceConnection->getTableName(self::TABLE),
            [
                self::FIELD_RATE_ID . ' IN (?)' => array_map(
                    function ($rate) {
                        return $rate->getId();
                    },
                    $rates
                )
            ]
        );
    }
}
