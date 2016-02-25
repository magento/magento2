<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Export;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_reindex_schedule.php
 */
class ProductStagingTest extends AbstractProductExportTestCase
{
    /**
     * Stock item attributes which must be exported
     *
     * @var array
     */
    public static $stockItemAttributes = [
        'qty',
        'min_qty',
        'use_config_min_qty',
        'is_qty_decimal',
        'backorders',
        'use_config_backorders',
        'min_sale_qty',
        'use_config_min_sale_qty',
        'max_sale_qty',
        'use_config_max_sale_qty',
        'is_in_stock',
        'notify_stock_qty',
        'use_config_notify_stock_qty',
        'manage_stock',
        'use_config_manage_stock',
        'use_config_qty_increments',
        'qty_increments',
        'use_config_enable_qty_inc',
        'enable_qty_increments',
        'is_decimal_divided'
    ];

    public function exportDataProvider()
    {
        return [
            'product_export_data' => [
                [
                    'Magento/CatalogImportExport/_files/product_export_data.php',
                    'Magento/CatalogImportExport/_files/create_product_update.php'
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
                    'Magento/Catalog/_files/products.php',
                    'Magento/CatalogImportExport/_files/create_product_update.php'
                ],
                [
                    'simple',
                    'custom-design-simple-product',
                ]
            ],
            */
            'simple-product' => [
                [
                    'Magento/Catalog/_files/product_simple.php',
                    'Magento/CatalogImportExport/_files/create_product_update.php'
                ],
                [
                    'simple',
                ]
            ],
            'simple-product-multistore' => [
                [
                    'Magento/Catalog/_files/product_simple_multistore.php',
                    'Magento/CatalogImportExport/_files/create_product_update.php'
                ],
                [
                    'simple',
                ]
            ],
            'simple-product-xss' => [
                [
                    'Magento/Catalog/_files/product_simple_xss.php',
                    'Magento/CatalogImportExport/_files/create_product_update.php'
                ],
                [
                    'product-with-xss',
                ]
            ],
            'simple-product-special-price' => [
                [
                    'Magento/Catalog/_files/product_special_price.php',
                    'Magento/CatalogImportExport/_files/create_product_update.php'
                ],
                [
                    'simple',
                ]
            ],
            'virtual-product' => [
                [
                    'Magento/Catalog/_files/product_virtual_in_stock.php',
                    'Magento/CatalogImportExport/_files/create_product_update.php'
                ],
                [
                    'virtual-product',
                ]
            ],
            'simple-product-options' => [
                [
                    'Magento/Catalog/_files/product_with_options.php',
                    'Magento/CatalogImportExport/_files/create_product_update.php'
                ],
                [
                    'simple',
                ]
            ],
            'simple-product-dropdown' => [
                [
                    'Magento/Catalog/_files/product_with_dropdown_option.php',
                    'Magento/CatalogImportExport/_files/create_product_update.php'
                ],
                [
                    'simple_dropdown_option',
                ]
            ],
            'simple-product-image' => [
                [
                    'Magento/Catalog/_files/product_with_image.php',
                    'Magento/CatalogImportExport/_files/create_product_update.php'
                ],
                [
                    'simple',
                ]
            ],
            // @todo uncomment after resolving MAGETWO-49676
            /*
            'simple-product-crosssell' => [
                [
                    'Magento/Catalog/_files/products_crosssell.php',
                    'Magento/CatalogImportExport/_files/create_product_update.php'
                ],
                [
                    'simple',
                ]
            ],
            'simple-product-related' => [
                [
                    'Magento/Catalog/_files/products_related_multiple.php',
                    'Magento/CatalogImportExport/_files/create_product_update.php'
                ]
                [
                    'simple',
                ]
            ],
            'simple-product-upsell' => [
                [
                    'Magento/Catalog/_files/products_upsell.php',
                    'Magento/CatalogImportExport/_files/create_product_update.php'
                ],
                [
                    'simple',
                ]
            ],
            */
        ];
    }
}
