<?php
/**
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab;

/**
 * Bundle product attributes tab
 */
class Attributes extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes
{
    /**
     * Prepare attributes form of bundle product
     *
     * @return void
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();

        $specialPrice = $this->getForm()->getElement('special_price');
        if ($specialPrice) {
            $specialPrice->setRenderer(
                $this->getLayout()->createBlock(
                    'Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Attributes\Special'
                )->setDisableChild(
                    false
                )
            );
            $specialPrice->addClass(
                implode(
                    ' ',
                    [
                        'validate-greater-than-zero',
                        'validate-number-range',
                        'number-range-0.00-100.00'
                    ]
                )
            );
        }

        $sku = $this->getForm()->getElement('sku');
        if ($sku) {
            $sku->setRenderer(
                $this->getLayout()->createBlock(
                    'Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Attributes\Extend'
                )->setDisableChild(
                    false
                )
            );
        }

        $price = $this->getForm()->getElement('price');
        if ($price) {
            $price->setRenderer(
                $this->getLayout()->createBlock(
                    'Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Attributes\Extend',
                    'adminhtml.catalog.product.bundle.edit.tab.attributes.price'
                )->setDisableChild(
                    true
                )
            );
        }

        $tax = $this->getForm()->getElement('tax_class_id');
        if ($tax) {
            $tax->setAfterElementHtml(
                '<script type="text/javascript">' .
                "
                //<![CDATA[
                function changeTaxClassId() {
                    if ($('price_type').value == '" .
                \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC .
                "') {
                        $('tax_class_id').disabled = true;
                        $('tax_class_id').value = '0';
                        $('tax_class_id').removeClassName('required-entry');
                        if ($('advice-required-entry-tax_class_id')) {
                            $('advice-required-entry-tax_class_id').remove();
                        }
                    } else {
                        $('tax_class_id').disabled = false;
                        " .
                ($tax->getRequired() ? "$('tax_class_id').addClassName('required-entry');" : '') .
                "
                    }
                }

                document.observe('dom:loaded', function() {
                    if ($('price_type')) {
                        $('price_type').observe('change', changeTaxClassId);
                        changeTaxClassId();
                    }
                });
                //]]>
                " .
                '</script>'
            );
        }

        $weight = $this->getForm()->getElement('weight');
        if ($weight) {
            $weight->setRenderer(
                $this->getLayout()->createBlock(
                    'Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Attributes\Extend'
                )->setDisableChild(
                    true
                )
            );
        }

        $tier_price = $this->getForm()->getElement('tier_price');
        if ($tier_price) {
            $tier_price->setRenderer(
                $this->getLayout()->createBlock(
                    'Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Price\Tier'
                )->setPriceColumnHeader(
                    __('Percent Discount')
                )->setPriceValidation(
                    'validate-greater-than-zero validate-number-range number-range-0.00-100.00'
                )
            );
        }

        $groupPrice = $this->getForm()->getElement('group_price');
        if ($groupPrice) {
            $groupPrice->setRenderer(
                $this->getLayout()->createBlock(
                    'Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Price\Group'
                )->setPriceColumnHeader(
                    __('Percent Discount')
                )->setPriceValidation(
                    'validate-greater-than-zero validate-number-range number-range-0.00-100.00'
                )
            );
        }
    }

    /**
     * Get current product from registry
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        if (!$this->getData('product')) {
            $this->setData('product', $this->_coreRegistry->registry('product'));
        }
        return $this->getData('product');
    }
}
