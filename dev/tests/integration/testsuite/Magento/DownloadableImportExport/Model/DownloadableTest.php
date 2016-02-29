<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DownloadableImportExport\Model;

use Magento\CatalogImportExport\Model\AbstractProductExportImportTestCase;

class DownloadableTest extends AbstractProductExportImportTestCase
{
    public function exportImportDataProvider()
    {
        return [
            'downloadable-product' => [
                [
                    'Magento/Downloadable/_files/product_downloadable.php'
                ],
                [
                    'downloadable-product',
                ],
            ],
            'downloadable-product-with-files' => [
                [
                    'Magento/Downloadable/_files/product_downloadable_with_files.php'
                ],
                [
                    'downloadable-product',
                ],
            ],
        ];
    }

    public function importReplaceDataProvider()
    {
        return $this->exportImportDataProvider();
    }

    /**
     * @param array $fixtures
     * @param string[] $skus
     * @param string[] $skippedAttributes
     * @dataProvider exportImportDataProvider
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @todo remove after MAGETWO-49467 resolved
     */
    public function testExport($fixtures, $skus, $skippedAttributes = [], $rollbackFixtures = [])
    {
        $this->markTestSkipped('Uncomment after MAGETWO-49467 resolved');
    }

    /**
     * @param array $fixtures
     * @param string[] $skus
     * @dataProvider exportImportDataProvider
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @todo remove after MAGETWO-49467 resolved
     */
    public function testImportDelete($fixtures, $skus, $skippedAttributes = [], $rollbackFixtures = [])
    {
        $this->markTestSkipped('Uncomment after MAGETWO-49467 resolved');
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     *
     * @param array $fixtures
     * @param string[] $skus
     * @param string[] $skippedAttributes
     * @dataProvider importReplaceDataProvider
     *
     * @todo remove after MAGETWO-49467 resolved
     */
    public function testImportReplace($fixtures, $skus, $skippedAttributes = [], $rollbackFixtures = [])
    {
        $this->markTestSkipped('Uncomment after MAGETWO-49467 resolved');
    }
}
