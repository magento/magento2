<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model;

/**
 * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_reindex_schedule.php
 */
class ProductTest extends AbstractProductExportImportTestCase
{
    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function exportImportDataProvider()
    {
        return [
            'product_export_data' => [
                [
                    'Magento/CatalogImportExport/_files/product_export_data.php'
                ],
                [
                    'simple_ms_1',
                    'simple_ms_2',
                    'simple',
                ]
            ],
            // @todo uncomment after resolving MAGETWO-49677
            /*
            'custom-design-simple-product' => [
                [
                    'Magento/Catalog/_files/products.php'
                ],
                [
                    'simple',
                    'custom-design-simple-product',
                ]
            ],
            */
            'simple-product' => [
                [
                    'Magento/Catalog/_files/product_simple.php'
                ],
                [
                    'simple',
                ]
            ],
            'simple-product-multistore' => [
                [
                    'Magento/Catalog/_files/product_simple_multistore.php'
                ],
                [
                    'simple',
                ]
            ],
            'simple-product-xss' => [
                [
                    'Magento/Catalog/_files/product_simple_xss.php'
                ],
                [
                    'product-with-xss',
                ]
            ],
            'simple-product-special-price' => [
                [
                    'Magento/Catalog/_files/product_special_price.php'
                ],
                [
                    'simple',
                ]
            ],
            'virtual-product' => [
                [
                    'Magento/Catalog/_files/product_virtual_in_stock.php'
                ],
                [
                    'virtual-product',
                ]
            ],
            'simple-product-options' => [
                [
                    'Magento/Catalog/_files/product_with_options.php'
                ],
                [
                    'simple',
                ]
            ],
            'simple-product-dropdown' => [
                [
                    'Magento/Catalog/_files/product_with_dropdown_option.php'
                ],
                [
                    'simple_dropdown_option',
                ]
            ],
            'simple-product-image' => [
                [
                    'Magento/CatalogImportExport/Model/Import/_files/media_import_image.php',
                    'Magento/Catalog/_files/product_with_image.php'
                ],
                [
                    'simple',
                ]
            ],
            'simple-product-crosssell' => [
                [
                    'Magento/Catalog/_files/products_crosssell.php'
                ],
                [
                    'simple',
                ]
            ],
            'simple-product-related' => [
                [
                    'Magento/Catalog/_files/products_related_multiple.php'
                ],
                [
                    'simple',
                ]
            ],
            'simple-product-upsell' => [
                [
                    'Magento/Catalog/_files/products_upsell.php'
                ],
                [
                    'simple',
                ]
            ],
        ];
    }

    public function importReplaceDataProvider()
    {
        return $this->exportImportDataProvider();
    }
}
