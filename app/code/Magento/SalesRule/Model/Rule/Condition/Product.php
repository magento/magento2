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
        $attributes['parent::quote_item_qty'] = __('Quantity in cart');
        $attributes['quote_item_price'] = __('Price in cart');
        $attributes['quote_item_row_total'] = __('Row total in cart');

        $attributes['parent::category_ids'] = __('Category (Parent only)');
        $attributes['children::category_ids'] = __('Category (Children Only)');
    }

    /**
     * Retrieve attribute
     *
     * @return string
     */
    public function getAttribute(): string
    {
        $attribute = $this->getData('attribute');
        if ($attribute !== null && strpos($attribute, '::') !== false) {
            list(, $attribute) = explode('::', $attribute);
        }

        return $attribute;
    }

    /**
     * @inheritdoc
     */
    public function getAttributeName()
    {
        $attribute = $this->getAttribute();
        if ($this->getAttributeScope()) {
            $attribute = $this->getAttributeScope() . '::' . $attribute;
        }

        return $this->getAttributeOption($attribute);
    }

    /**
     * @inheritdoc
     */
    public function loadAttributeOptions()
    {
        $productAttributes = $this->_productResource->loadAllAttributes()->getAttributesByCode();

        $attributes = [];
        foreach ($productAttributes as $attribute) {
            /* @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
            if (!$attribute->isAllowedForRuleCondition()
                || !$attribute->getDataUsingMethod($this->_isUsedForRuleProperty)
            ) {
                continue;
            }
            $frontLabel = $attribute->getFrontendLabel();
            $attributes[$attribute->getAttributeCode()] = $frontLabel;
            $attributes['parent::' . $attribute->getAttributeCode()] = $frontLabel . __('(Parent Only)');
            $attributes['children::' . $attribute->getAttributeCode()] = $frontLabel . __('(Children Only)');
        }

        $this->_addSpecialAttributes($attributes);

        asort($attributes);
        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAttributeElementHtml()
    {
        $html = parent::getAttributeElementHtml() .
                $this->getAttributeScopeElement()->getHtml();

        return $html;
    }

    /**
     * Retrieve form element for scope element
     *
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    private function getAttributeScopeElement(): \Magento\Framework\Data\Form\Element\AbstractElement
    {
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__attribute_scope',
            'hidden',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][attribute_scope]',
                'value' => $this->getAttributeScope(),
                'no_span' => true,
                'class' => 'hidden',
                'data-form-part' => $this->getFormName(),
            ]
        );
    }

    /**
     * Set attribute value
     *
     * @param string $value
     * @return void
     */
    public function setAttribute(string $value)
    {
        if (strpos($value, '::') !== false) {
            list($scope, $attribute) = explode('::', $value);
            $this->setData('attribute_scope', $scope);
            $this->setData('attribute', $attribute);
        } else {
            $this->setData('attribute', $value);
        }
    }

    /**
     * @inheritdoc
     */
    public function loadArray($arr)
    {
        parent::loadArray($arr);
        $this->setAttributeScope($arr['attribute_scope'] ?? null);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function asArray(array $arrAttributes = [])
    {
        $out = parent::asArray($arrAttributes);
        $out['attribute_scope'] = $this->getAttributeScope();

        return $out;
    }

    /**
     * Validate Product Rule Condition
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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

        $attrCode = $this->getAttribute();

        if ($attrCode === 'category_ids') {
            return $this->validateAttribute($this->_getAvailableInCategories($product->getId()));
        }

        if ($attrCode === 'quote_item_price') {
            $numericOperations = $this->getDefaultOperatorInputByType()['numeric'];
            if (in_array($this->getOperator(), $numericOperations)) {
                $this->setData('value', $this->getFormattedPrice($this->getValue()));
            }
        }

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

    /**
     * Get locale-based formatted price.
     *
     * @param string $value
     * @return float|null
     */
    private function getFormattedPrice($value)
    {
        $value = preg_replace('/[^0-9^\^.,-]/m', '', $value);

        /**
         * If the comma is the third symbol in the number, we consider it to be a decimal separator
         */
        $separatorComa = strpos($value, ',');
        $separatorDot = strpos($value, '.');
        if ($separatorComa !== false && $separatorDot === false && preg_match('/,\d{3}$/m', $value) === 1) {
            $value .= '.00';
        }
        return $this->_localeFormat->getNumber($value);
    }
}
