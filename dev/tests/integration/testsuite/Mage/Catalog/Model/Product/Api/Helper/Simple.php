<?php
/**
 * Simple product tests helper.
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Catalog_Model_Product_Api_Helper_Simple extends PHPUnit_Framework_TestCase
{
    /**
     * Load simple product fixture data
     *
     * @param string $fixtureName
     * @return array
     */
    public function loadSimpleProductFixtureData($fixtureName)
    {
        return require '_fixture/_data/Catalog/Product/Simple/' . $fixtureName . '.php';
    }

    /**
     * Check simple product attributes
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $expectedProductData
     * @param array $skipAttributes
     * @param array $skipStockItemAttrs
     */
    public function checkSimpleAttributesData(
        $product,
        $expectedProductData,
        $skipAttributes = array(),
        $skipStockItemAttrs = array()
    ) {
        $expectedProductData = array_diff_key($expectedProductData, array_flip($skipAttributes));

        $dateAttributes = array(
            'news_from_date',
            'news_to_date',
            'special_from_date',
            'special_to_date',
            'custom_design_from',
            'custom_design_to'
        );
        foreach ($dateAttributes as $attribute) {
            if (isset($expectedProductData[$attribute])) {
                $this->assertEquals(
                    strtotime($expectedProductData[$attribute]),
                    strtotime($product->getData($attribute))
                );
            }
        }

        $exclude = array_merge(
            $dateAttributes,
            array(
                'group_price',
                'tier_price',
                'stock_data',
                'url_key',
                'url_key_create_redirect'
            )
        );
        // Validate URL Key - all special chars should be replaced with dash sign
        $this->assertEquals('123-abc', $product->getUrlKey());
        $productAttributes = array_diff_key($expectedProductData, array_flip($exclude));
        foreach ($productAttributes as $attribute => $value) {
            $this->assertEquals($value, $product->getData($attribute), 'Invalid attribute "' . $attribute . '"');
        }

        if (isset($expectedProductData['stock_data'])) {
            $stockItem = $product->getStockItem();
            $expectedStock = array_diff_key($expectedProductData['stock_data'], array_flip($skipStockItemAttrs));
            foreach ($expectedStock as $attribute => $value) {
                $this->assertEquals(
                    $value,
                    $stockItem->getData($attribute),
                    'Invalid stock_data attribute "' . $attribute . '"'
                );
            }
        }
    }

    /**
     * Check stock item use default flags
     *
     * @param Mage_Catalog_Model_Product $product
     */
    public function checkStockItemDataUseDefault($product)
    {
        $stockItem = $product->getStockItem();
        $this->assertNotNull($stockItem);
        $fields = array(
            'use_config_min_qty',
            'use_config_min_sale_qty',
            'use_config_max_sale_qty',
            'use_config_backorders',
            'use_config_notify_stock_qty',
            'use_config_enable_qty_inc'
        );
        foreach ($fields as $field) {
            $this->assertEquals(1, $stockItem->getData($field), $field . ' is not set to 1');
        }
    }
}
