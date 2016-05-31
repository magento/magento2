<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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

    /**
     * @param \Magento\Catalog\Model\Product $expectedProduct
     * @param \Magento\Catalog\Model\Product $actualProduct
     */
    protected function assertEqualsSpecificAttributes($expectedProduct, $actualProduct)
    {
        $expectedProductLinks   = $expectedProduct->getExtensionAttributes()->getDownloadableProductLinks();
        $expectedProductSamples = $expectedProduct->getExtensionAttributes()->getDownloadableProductSamples();

        $actualProductLinks   = $actualProduct->getExtensionAttributes()->getDownloadableProductLinks();
        $actualProductSamples = $actualProduct->getExtensionAttributes()->getDownloadableProductSamples();

        $this->assertEquals(count($expectedProductLinks), count($actualProductLinks));
        $this->assertEquals(count($expectedProductSamples), count($actualProductSamples));

        $expectedLinksArray = [];
        foreach ($expectedProductLinks as $link) {
            $expectedLinksArray[] = $link->getData();
        }
        foreach ($actualProductLinks as $actualLink) {
            $this->assertContains($expectedLinksArray, $actualLink->getData());
        }

        $expectedSamplesArray = [];
        foreach ($expectedProductSamples as $sample) {
            $expectedSamplesArray[] = $sample->getData();
        }
        foreach ($actualProductSamples as $actualSample) {
            $this->assertContains($expectedSamplesArray, $actualSample->getData());
        }
    }
}
