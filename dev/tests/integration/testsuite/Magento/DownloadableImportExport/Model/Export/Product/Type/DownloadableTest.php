<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DownloadableImportExport\Model\Export\Product\Type;

use Magento\CatalogImportExport\Model\Export\AbstractProductExportTestCase;

class DownloadableTest extends AbstractProductExportTestCase
{
    public function exportDataProvider()
    {
        return [
            'downloadable-product' => [
                'Magento/Downloadable/_files/product_downloadable.php',
                [
                    'downloadable-product',
                ],
            ],
            'downloadable-product-with-files' => [
                'Magento/Downloadable/_files/product_downloadable_with_files.php',
                [
                    'downloadable-product',
                ],
            ],
        ];
    }
}
