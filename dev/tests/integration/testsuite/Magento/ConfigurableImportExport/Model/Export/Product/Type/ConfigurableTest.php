<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableImportExport\Model\Export\Product\Type;

use Magento\CatalogImportExport\Model\Export\AbstractProductExportTestCase;

class ConfigurableTest extends AbstractProductExportTestCase
{
    public function exportDataProvider()
    {
        return [
            'configurable-product' => [
                'Magento/ConfigurableProduct/_files/product_configurable.php',
                [
                    'configurable',
                ],
                ['_cache_instance_products', '_cache_instance_configurable_attributes'],
            ],
        ];
    }
}
