<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Cron;

class ImportDataTableCleanup
{
    /**
     * DB data source model.
     *
     * @var \Magento\ImportExport\Model\ResourceModel\Import\Data
     */
    private $dataSourceModel;

    /**
     * @param \Magento\ImportExport\Model\ResourceModel\Import\Data $importData
     */
    public function __construct(
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData
    ) {
        $this->dataSourceModel = $importData;
    }

    /**
     * Remove all rows from importexport_importdata table
     *
     * @return void
     */
    public function execute()
    {
        $this->dataSourceModel->cleanProcessedBunches();
    }
}
