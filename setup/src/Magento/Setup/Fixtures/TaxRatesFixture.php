<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

/**
 * Class TaxRatesFixture
 */
class TaxRatesFixture extends Fixture
{
    /**
     * @var int
     */
    protected $priority = 90;

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $taxRatesFile = $this->fixtureModel->getValue('tax_rates_file', null);
        if (empty($taxRatesFile)) {
            return;
        }
        $this->fixtureModel->resetObjectManager();
        /** Clean predefined tax rates to maintain consistency */
        /** @var $collection Magento\Tax\Model\ResourceModel\Calculation\Rate\Collection */
        $collection = $this->fixtureModel->getObjectManager()
            ->get('Magento\Tax\Model\ResourceModel\Calculation\Rate\Collection');

        /** @var $model Magento\Tax\Model\Calculation\Rate */
        $model = $this->fixtureModel->getObjectManager()
            ->get('Magento\Tax\Model\Calculation\Rate');

        foreach ($collection->getAllIds() as $id) {
            $model->setId($id);
            $model->delete();
        }
        /**
         * Import tax rates with import handler
         */
        $filename = realpath(__DIR__ . '/' . $taxRatesFile);
        $file = [
            'name' => $filename,
            'type' => 'fixtureModel/vnd.ms-excel',
            'tmp_name' => $filename,
            'error' => 0,
            'size' => filesize($filename),
        ];
        $importHandler = $this->fixtureModel->getObjectManager()
            ->create('Magento\TaxImportExport\Model\Rate\CsvImportHandler');
        $importHandler->importFromCsvFile($file);

    }

    /**
     * {@inheritdoc}
     */
    public function getActionTitle()
    {
        return 'Generating tax rates';
    }

    /**
     * {@inheritdoc}
     */
    public function introduceParamLabels()
    {
        return [];
    }
}
