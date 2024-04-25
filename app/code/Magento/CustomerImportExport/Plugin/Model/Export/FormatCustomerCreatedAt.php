<?php
/************************************************************************
 * Copyright 2024 Adobe
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
namespace Magento\CustomerImportExport\Plugin\Model\Export;

use Magento\CustomerImportExport\Model\Export\Customer;
use Magento\Customer\Model\Customer as Item;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Plugin for format the Customer item created at date column
 *
 */
class FormatCustomerCreatedAt
{
    private const COLUMN_CREATED_AT = 'created_at';

    /**
     * @param TimezoneInterface $localeDate
     */
    public function __construct(
        private TimezoneInterface $localeDate
    ) {
    }

    /**
     * Format the created_at column based on the timezone configuration.
     *
     * @param Customer $subject
     * @param Item $item
     * @return void
     */
    public function beforeExportItem(
        Customer $subject,
        Item $item
    ): void
    {
        $item->setData(
            self::COLUMN_CREATED_AT,
            $this->localeDate->formatDate(
                $item->getData(self::COLUMN_CREATED_AT),
                \IntlDateFormatter::MEDIUM,
                true
            )
        );
    }
}
