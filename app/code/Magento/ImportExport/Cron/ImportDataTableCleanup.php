<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Cron;

class ImportDataTableCleanup
{
    /**
     * DB data source model.
     *
     * @var \Magento\ImportExport\Model\ResourceModel\Import\Data
     */
    protected $_dataSourceModel;

    /**
     * @param \Magento\ImportExport\Model\ResourceModel\Import\Data $importData
     */
    public function __construct(
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData
    ) {
        $this->_dataSourceModel = $importData;
    }

    /**
     * Remove all rows from importexport_importdata table
     *
     * @return void
     */
    public function execute()
    {
        $this->_dataSourceModel->cleanProcessedBunches();
    }
}
