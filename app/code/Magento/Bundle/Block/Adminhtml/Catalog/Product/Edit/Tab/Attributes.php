<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

namespace Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

/**
 * Bundle product attributes tab
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Attributes extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes
{
    /**
     * @var SecureHtmlRenderer
     */
    protected $secureRenderer;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     * @param SecureHtmlRenderer|null $htmlRenderer
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = [],
        ?SecureHtmlRenderer $htmlRenderer = null
    ) {
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $data
        );
        $this->secureRenderer = $htmlRenderer ?? ObjectManager::getInstance()->get(SecureHtmlRenderer::class);
    }

    /**
     * Prepare attributes form of bundle product
     *
     * @return void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();

        $specialPrice = $this->getForm()->getElement('special_price');
        if ($specialPrice) {
            $specialPrice->setRenderer(
                $this->getLayout()->createBlock(
                    \Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Attributes\Special::class
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
                    \Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Attributes\Extend::class
                )->setDisableChild(
                    false
                )
            );
        }

        $price = $this->getForm()->getElement('price');
        if ($price) {
            $price->setRenderer(
                $this->getLayout()->createBlock(
                    \Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Attributes\Extend::class,
                    'adminhtml.catalog.product.bundle.edit.tab.attributes.price'
                )->setDisableChild(
                    true
                )
            );
        }

        $tax = $this->getForm()->getElement('tax_class_id');
        if ($tax) {
            $scriptString = "
                require(['prototype'], function(){
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

                if ($('price_type')) {
                    $('price_type').observe('change', changeTaxClassId);
                    changeTaxClassId();
                }
                });
                ";

            $tax->setAfterElementHtml($this->secureRenderer->renderTag('script', [], $scriptString, false));
        }

        $weight = $this->getForm()->getElement('weight');
        if ($weight) {
            $weight->setRenderer(
                $this->getLayout()->createBlock(
                    \Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Attributes\Extend::class
                )->setDisableChild(
                    true
                )
            );
        }

        $tier_price = $this->getForm()->getElement('tier_price');
        if ($tier_price) {
            $tier_price->setRenderer(
                $this->getLayout()->createBlock(
                    \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Price\Tier::class
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
