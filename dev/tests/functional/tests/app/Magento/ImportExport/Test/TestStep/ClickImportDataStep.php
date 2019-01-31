<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\TestStep;

use Magento\ImportExport\Test\Page\Adminhtml\AdminImportIndex;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Click "Import" button.
 */
class ClickImportDataStep implements TestStepInterface
{
    /**
     * Import index page.
     *
     * @var AdminImportIndex
     */
    private $adminImportIndex;

    /**
     * @param AdminImportIndex $adminImportIndex
     */
    public function __construct(AdminImportIndex $adminImportIndex)
    {
        $this->adminImportIndex = $adminImportIndex;
    }

    /**
     * Click "Import" button.
     *
     * @return void
     */
    public function run()
    {
        $this->adminImportIndex->getImportResult()->clickImportButton();
    }
}
