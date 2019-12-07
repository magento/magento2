<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\TestStep;

use Magento\ImportExport\Test\Page\Adminhtml\AdminImportIndex;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Open import index page.
 */
class OpenImportIndexStep implements TestStepInterface
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
     * Open import index page.
     *
     * @return void
     */
    public function run()
    {
        $this->adminImportIndex->open();
    }
}
