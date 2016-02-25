<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedImportExport\Model;

use Magento\CatalogImportExport\Model\AbstractProductExportImportTestCase;

class GroupedTest extends AbstractProductExportImportTestCase
{
    public function exportImportDataProvider()
    {
        return [
            'grouped-product' => [
                'Magento/GroupedProduct/_files/product_grouped.php',
                [
                    'grouped-product',
                ]
            ],
        ];
    }
}
