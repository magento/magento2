<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedImportExport\Model\Export\Product\Type;

use Magento\CatalogImportExport\Model\Export\AbstractProductExportTestCase;

class GroupedTest extends AbstractProductExportTestCase
{
    public function exportDataProvider()
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
