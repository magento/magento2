<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Rule\Condition;

/**
 * Product rule condition data model
 *
 * @author Magento Core Team <core@magentocommerce.com>
 */
class Product extends \Magento\Rule\Model\Condition\Product\AbstractProduct
{
    /**
     * Add special attributes
     *
     * @param array $attributes
     * @return void
     */
    protected function _addSpecialAttributes(array &$attributes)
    {
        parent::_addSpecialAttributes($attributes);
        $attributes['quote_item_qty'] = __('Quantity in cart');
        $attributes['quote_item_price'] = __('Price in cart');
        $attributes['quote_item_row_total'] = __('Row total in cart');
    }

    /**
     * Validate Product Rule Condition
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        //@todo reimplement this method when is fixed MAGETWO-5713
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $model->getProduct();
        if (!$product instanceof \Magento\Catalog\Model\Product) {
            $product = $this->productRepository->getById($model->getProductId());
        }

        $product->setQuoteItemQty(
            $model->getQty()
        )->setQuoteItemPrice(
            $model->getPrice() // possible bug: need to use $model->getBasePrice()
        )->setQuoteItemRowTotal(
            $model->getBaseRowTotal()
        );

        return parent::validate($product);
    }

    /**
     * Retrieve value element chooser URL
     *
     * @return string
     */
    public function getValueElementChooserUrl()
    {
        $url = false;
        switch ($this->getAttribute()) {
            case 'sku':
            case 'category_ids':
                $url = 'sales_rule/promo_widget/chooser/attribute/' . $this->getAttribute();
                if ($this->getJsFormObject()) {
                    $url .= '/form/' . $this->getJsFormObject();
                }
                break;
            default:
                break;
        }
        return $url !== false ? $this->_backendData->getUrl($url) : '';
    }
}
