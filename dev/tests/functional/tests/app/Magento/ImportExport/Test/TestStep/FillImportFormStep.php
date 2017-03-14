<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\TestStep;

use Magento\ImportExport\Test\Fixture\Import\File;
use Magento\ImportExport\Test\Fixture\ImportData;
use Magento\ImportExport\Test\Page\Adminhtml\AdminImportIndex;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Mtf\TestStep\TestStepFactory;

/**
 * Fill import form.
 */
class FillImportFormStep implements TestStepInterface
{
    /**
     * Import index page.
     *
     * @var AdminImportIndex
     */
    private $adminImportIndex;

    /**
     * Import fixture.
     *
     * @var ImportData
     */
    private $import;

    /**
     * @param AdminImportIndex $adminImportIndex
     * @param ImportData $import
     * @param TestStepFactory $stepFactory
     * @param bool|null $changeCurrency
     */
    public function __construct(
        AdminImportIndex $adminImportIndex,
        ImportData $import,
        TestStepFactory $stepFactory,
        $changeCurrency = false
    ) {
        $this->adminImportIndex = $adminImportIndex;
        $this->import = $import;

        if ($changeCurrency === true) {
            $currency = $import->getDataFieldConfig('import_file')['source']->getValue()['template']['websiteCurrency'];
            $stepFactory->create(
                ChangeCurrencyOnCustomWebsiteStep::class,
                ['import' => $this->import, 'currency' => $currency]
            )->run();
        }
    }

    /**
     * Fill import form.
     *
     * @return array
     */
    public function run()
    {
        $this->adminImportIndex->getImportForm()->fill($this->import);

        /** @var File $file */
        $file = $this->import->getDataFieldConfig('import_file')['source'];

        return [
            'entities' => $file->getEntities(),
            'import' => $this->import,
            'products' => $file->getEntities()
        ];
    }
}
