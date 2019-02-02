<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DownloadableImportExport\Model;

use Magento\CatalogImportExport\Model\AbstractProductExportImportTestCase;

class DownloadableTest extends AbstractProductExportImportTestCase
{
    /**
     * @return array
     */
    public function exportImportDataProvider(): array
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

    /**
     * Run import/export tests.
     *
     * @magentoAppArea adminhtml
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     *
     * @param array $fixtures
     * @param string[] $skus
     * @param string[] $skippedAttributes
     * @return void
     * @dataProvider exportImportDataProvider
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testImportExport(array $fixtures, array $skus, array $skippedAttributes = []): void
    {
        $this->markTestSkipped('Uncomment after MAGETWO-38240 resolved');
    }

    /**
     * @inheritdoc
     */
    protected function assertEqualsSpecificAttributes(
        \Magento\Catalog\Model\Product $expectedProduct,
        \Magento\Catalog\Model\Product $actualProduct
    ): void {
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
