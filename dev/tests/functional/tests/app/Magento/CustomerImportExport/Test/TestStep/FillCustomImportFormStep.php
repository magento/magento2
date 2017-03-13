<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerImportExport\Test\TestStep;

use Magento\ImportExport\Test\TestStep\FillImportFormStep;
use Magento\ImportExport\Test\Fixture\Import\File;
use Magento\ImportExport\Test\Fixture\ImportData;
use Magento\ImportExport\Test\Page\Adminhtml\AdminImportIndex;
use Magento\Mtf\TestStep\TestStepFactory;

/**
 * Fill custom import form.
 */
class FillCustomImportFormStep extends FillImportFormStep
{
    /**
     * @param AdminImportIndex $adminImportIndex
     * @param ImportData $import
     * @param TestStepFactory $stepFactory
     */
    public function __construct(
        AdminImportIndex $adminImportIndex,
        ImportData $import,
        TestStepFactory $stepFactory
    ) {
        parent::__construct($adminImportIndex, $import, $stepFactory, false);
    }

    /**
     * Fill import form.
     *
     * @return array
     */
    public function run()
    {
        $this->adminImportIndex->getCustomImportForm()->fill($this->import);

        /** @var File $file */
        $file = $this->import->getDataFieldConfig('import_file')['source'];

        return [
            'entities' => $file->getEntities(),
            'import' => $this->import
        ];
    }
}
