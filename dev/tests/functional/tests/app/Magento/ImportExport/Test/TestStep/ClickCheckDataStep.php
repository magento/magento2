<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\TestStep;

use Magento\ImportExport\Test\Constraint\AssertImportCheckData;
use Magento\ImportExport\Test\Page\Adminhtml\AdminImportIndex;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\ImportExport\Test\Fixture\ImportData;

/**
 * Click "Check Data" button.
 */
class ClickCheckDataStep implements TestStepInterface
{
    /**
     * Import index page.
     *
     * @var AdminImportIndex
     */
    private $adminImportIndex;

    /**
     * Assert that validation result message is correct.
     *
     * @var AssertImportCheckData
     */
    private $assert;

    /**
     * Import fixture.
     *
     * @var ImportData
     */
    private $import;

    /**
     * @param AdminImportIndex $adminImportIndex
     * @param AssertImportCheckData $assert
     * @param ImportData $import
     */
    public function __construct(AdminImportIndex $adminImportIndex, AssertImportCheckData $assert, ImportData $import)
    {
        $this->adminImportIndex = $adminImportIndex;
        $this->assert = $assert;
        $this->import = $import;
    }

    /**
     * Click "Check Data" button.
     *
     * @return void
     */
    public function run()
    {
        $this->adminImportIndex->getFormPageActions()->clickCheckData();
    }
}
