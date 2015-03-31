<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Class TaxRatesFixture
 */
class TaxRatesFixture extends \Magento\ToolkitFramework\Fixture
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
        $this->application->resetObjectManager();
        /** Clean predefined tax rates to maintain consistency */
        /** @var $collection Magento\Tax\Model\Resource\Calculation\Rate\Collection */
        $collection = $this->application->getObjectManager()
            ->get('Magento\Tax\Model\Resource\Calculation\Rate\Collection');

        /** @var $model Magento\Tax\Model\Calculation\Rate */
        $model = $this->application->getObjectManager()
            ->get('Magento\Tax\Model\Calculation\Rate');

        foreach ($collection->getAllIds() as $id) {
            $model->setId($id);
            $model->delete();
        }
        /**
         * Import tax rates with import handler
         */
        $filename = realpath(__DIR__ . '/tax_rates.csv');
        $file = [
            'name' => $filename,
            'type' => 'application/vnd.ms-excel',
            'tmp_name' => $filename,
            'error' => 0,
            'size' => filesize($filename),
        ];
        $importHandler = $this->application->getObjectManager()
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

return new TaxRatesFixture($this);
