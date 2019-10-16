<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Controller\Adminhtml;

use Magento\Bundle\Api\Data\OptionInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Provide tests for product admin controllers.
 * @magentoAppArea adminhtml
 */
class ProductTest extends AbstractBackendController
{
    /**
     * Test bundle product duplicate won't remove bundle options from original product.
     *
     * @magentoDataFixture Magento/Catalog/_files/products_new.php
     * @return void
     */
    public function testDuplicateProduct()
    {
        $params = $this->getRequestParamsForDuplicate();
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setParams(['type' => Type::TYPE_BUNDLE]);
        $this->getRequest()->setPostValue($params);
        $this->dispatch('backend/catalog/product/save');
        $this->assertSessionMessages(
            $this->equalTo(
                [
                    'You saved the product.',
                    'You duplicated the product.',
                ]
            ),
            MessageInterface::TYPE_SUCCESS
        );
        $this->assertOptions();
    }

    /**
     * Get necessary request post params for creating and duplicating bundle product.
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function getRequestParamsForDuplicate()
    {
        $product = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class)->get('simple');
        return [
            'product' =>
                [
                    'attribute_set_id' => '4',
                    'gift_message_available' => '0',
                    'use_config_gift_message_available' => '1',
                    'stock_data' =>
                        [
                            'min_qty_allowed_in_shopping_cart' =>
                                [
                                    [
                                        'record_id' => '0',
                                        'customer_group_id' => '32000',
                                        'min_sale_qty' => '',
                                    ],
                                ],
                            'min_qty' => '0',
                            'max_sale_qty' => '10000',
                            'notify_stock_qty' => '1',
                            'min_sale_qty' => '1',
                            'qty_increments' => '1',
                            'use_config_manage_stock' => '1',
                            'manage_stock' => '1',
                            'use_config_min_qty' => '1',
                            'use_config_max_sale_qty' => '1',
                            'use_config_backorders' => '1',
                            'backorders' => '0',
                            'use_config_notify_stock_qty' => '1',
                            'use_config_enable_qty_inc' => '1',
                            'enable_qty_increments' => '0',
                            'use_config_qty_increments' => '1',
                            'use_config_min_sale_qty' => '1',
                            'is_qty_decimal' => '0',
                            'is_decimal_divided' => '0',
                        ],
                    'status' => '1',
                    'affect_product_custom_options' => '1',
                    'name' => 'b1',
                    'price' => '',
                    'weight' => '',
                    'url_key' => '',
                    'special_price' => '',
                    'quantity_and_stock_status' =>
                        [
                            'qty' => '',
                            'is_in_stock' => '1',
                        ],
                    'sku_type' => '0',
                    'price_type' => '0',
                    'weight_type' => '0',
                    'website_ids' =>
                        [
                            1 => '1',
                        ],
                    'sku' => 'b1',
                    'meta_title' => 'b1',
                    'meta_keyword' => 'b1',
                    'meta_description' => 'b1 ',
                    'tax_class_id' => '2',
                    'product_has_weight' => '1',
                    'visibility' => '4',
                    'country_of_manufacture' => '',
                    'page_layout' => '',
                    'options_container' => 'container2',
                    'custom_design' => '',
                    'custom_layout' => '',
                    'price_view' => '0',
                    'shipment_type' => '0',
                    'news_from_date' => '',
                    'news_to_date' => '',
                    'custom_design_from' => '',
                    'custom_design_to' => '',
                    'special_from_date' => '',
                    'special_to_date' => '',
                    'description' => '',
                    'short_description' => '',
                    'custom_layout_update' => '',
                    'image' => '',
                    'small_image' => '',
                    'thumbnail' => '',
                ],
            'bundle_options' =>
                [
                    'bundle_options' =>
                        [
                            [
                                'record_id' => '0',
                                'type' => 'select',
                                'required' => '1',
                                'title' => 'test option title',
                                'position' => '1',
                                'option_id' => '',
                                'delete' => '',
                                'bundle_selections' =>
                                    [
                                        [
                                            'product_id' => $product->getId(),
                                            'name' => $product->getName(),
                                            'sku' => $product->getSku(),
                                            'price' => $product->getPrice(),
                                            'delete' => '',
                                            'selection_can_change_qty' => '',
                                            'selection_id' => '',
                                            'selection_price_type' => '0',
                                            'selection_price_value' => '',
                                            'selection_qty' => '1',
                                            'position' => '1',
                                            'option_id' => '',
                                            'record_id' => '1',
                                            'is_default' => '0',
                                        ],
                                    ],
                                'bundle_button_proxy' =>
                                    [
                                        [
                                            'entity_id' => '1',
                                        ],
                                    ],
                            ],
                        ],
                ],
            'affect_bundle_product_selections' => '1',
            'back' => 'duplicate',
            'form_key' => Bootstrap::getObjectManager()->get(FormKey::class)->getFormKey(),
        ];
    }

    /**
     * Check options in created and duplicated products.
     *
     * @return void
     */
    private function assertOptions()
    {
        $createdOptions = $this->getProductOptions('b1');
        $createdOption = array_shift($createdOptions);
        $duplicatedOptions = $this->getProductOptions('b1-1');
        $duplicatedOption = array_shift($duplicatedOptions);
        $this->assertNotEmpty($createdOption);
        $this->assertNotEmpty($duplicatedOption);
        $optionFields = ['type', 'title', 'position', 'required', 'default_title'];
        foreach ($optionFields as $field) {
            $this->assertSame($createdOption->getData($field), $duplicatedOption->getData($field));
        }
        $createdLinks = $createdOption->getProductLinks();
        $createdLink = array_shift($createdLinks);
        $duplicatedLinks = $duplicatedOption->getProductLinks();
        $duplicatedLink = array_shift($duplicatedLinks);
        $this->assertNotEmpty($createdLink);
        $this->assertNotEmpty($duplicatedLink);
        $linkFields = [
            'entity_id',
            'sku',
            'position',
            'is_default',
            'price',
            'qty',
            'selection_can_change_quantity',
            'price_type',
        ];
        foreach ($linkFields as $field) {
            $this->assertSame($createdLink->getData($field), $duplicatedLink->getData($field));
        }
    }

    /**
     * Get options for given product.
     *
     * @param string $sku
     * @return OptionInterface[]
     */
    private function getProductOptions(string $sku)
    {
        $product = Bootstrap::getObjectManager()->create(Product::class);
        $productId = $product->getResource()->getIdBySku($sku);
        $product->load($productId);

        return $product->getExtensionAttributes()->getBundleProductOptions();
    }
}
