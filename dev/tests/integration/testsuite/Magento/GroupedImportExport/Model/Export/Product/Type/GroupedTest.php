<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedImportExport\Model\Export\Product\Type;

use Magento\CatalogImportExport\Model\Export\AbstractProductExportTestCase;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\ImportExport\Model\Import;

class GroupedTest extends AbstractProductExportTestCase
{
    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     *
     * @magentoDataFixture Magento/GroupedProduct/_files/product_grouped.php
     */
    public function testExport()
    {
        $this->executeExportTest(['grouped-product']);
    }
}
