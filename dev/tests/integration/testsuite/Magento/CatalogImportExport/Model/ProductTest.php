<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
                    'Magento/Catalog/_files/product_with_image.php',
                ],
                [
                    'simple',
                ],
                [
                    "image",
                    "small_image",
                    "thumbnail",
                    "media_gallery"
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

    /**
     * Fixing https://github.com/magento-engcom/import-export-improvements/issues/50 means that during import images
     * can now get renamed for this we need to skip the attribute checking and instead check that the images contain
     * the right beginning part of the name. When an image is named "magento_image.jpeg" but there is already an image
     * with that name it will now become "magento_image_1.jpeg"
     *
     * @param \Magento\Catalog\Model\Product $expectedProduct
     * @param \Magento\Catalog\Model\Product $actualProduct
     */
    protected function assertEqualsSpecificAttributes($expectedProduct, $actualProduct)
    {
        if (!empty($actualProduct->getImage())
            && !empty($expectedProduct->getImage())
        ) {
            $this->assertContains('magento_image', $actualProduct->getImage());
        }
        if (!empty($actualProduct->getSmallImage())
            && !empty($expectedProduct->getSmallImage())
        ) {
            $this->assertContains('magento_image', $actualProduct->getSmallImage());
        }
        if (!empty($actualProduct->getThumbnail())
            && !empty($expectedProduct->getThumbnail())
        ) {
            $this->assertContains('magento_image', $actualProduct->getThumbnail());
        }
    }
}
