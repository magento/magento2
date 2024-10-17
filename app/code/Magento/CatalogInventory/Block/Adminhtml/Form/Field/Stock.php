<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * HTML select element block
 */
namespace Magento\CatalogInventory\Block\Adminhtml\Form\Field;

use Magento\Framework\Data\Form;

/**
 * @api
 * @since 100.0.2
 *
 * @deprecated 100.3.0 Replaced with Multi Source Inventory
 * @link https://developer.adobe.com/commerce/webapi/rest/inventory/index.html
 * @link https://developer.adobe.com/commerce/webapi/rest/inventory/inventory-api-reference.html
 */
class Stock extends \Magento\Framework\Data\Form\Element\Select
{
    const QUANTITY_FIELD_HTML_ID = 'qty';

    /**
     * Quantity field element
     *
     * @var \Magento\Framework\Data\Form\Element\Text
     */
    protected $_qty;

    /**
     * Is product composite
     *
     * @var bool
     */
    protected $_isProductComposite;

    /**
     * Text element factory
     *
     * @var \Magento\Framework\Data\Form\Element\TextFactory
     */
    protected $_factoryText;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\Data\Form\Element\TextFactory $factoryText
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Data\Form\Element\TextFactory $factoryText,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_factoryText = $factoryText;
        $this->_qty = isset($data['qty']) ? $data['qty'] : $this->_createQtyElement();
        unset($data['qty']);
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->coreRegistry = $coreRegistry;
        $this->setName($data['name']);
    }

    /**
     * Create quantity field
     *
     * @return \Magento\Framework\Data\Form\Element\Text
     */
    protected function _createQtyElement()
    {
        /** @var \Magento\Framework\Data\Form\Element\Text $element */
        $element = $this->_factoryText->create();
        $element->setId(self::QUANTITY_FIELD_HTML_ID)->setName('qty')->addClass('validate-number input-text');
        return $element;
    }

    /**
     * Join quantity and in stock elements' html
     *
     * @return string
     */
    public function getElementHtml()
    {
        $this->_disableFields();
        return $this->_qty->getElementHtml() . parent::getElementHtml() . $this->_getJs(
            self::QUANTITY_FIELD_HTML_ID,
            $this->getId()
        );
    }

    /**
     * Set form to quantity element in addition to current element
     *
     * @param Form $form
     * @return Form
     */
    public function setForm($form)
    {
        $this->_qty->setForm($form);
        return parent::setForm($form);
    }

    /**
     * Set value to quantity element in addition to current element
     *
     * @param array|string $value
     * @return $this
     */
    public function setValue($value)
    {
        if (is_array($value) && isset($value['qty'])) {
            $this->_qty->setValue($value['qty']);
        }
        parent::setValue(is_array($value) && isset($value['is_in_stock']) ? $value['is_in_stock'] : $value);
        return $this;
    }

    /**
     * Set name to quantity element in addition to current element
     *
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->_qty->setName($name . '[qty]');
        parent::setName($name . '[is_in_stock]');
    }

    /**
     * Get whether product is composite
     *
     * @return bool
     */
    protected function _isProductComposite()
    {
        if ($this->_isProductComposite === null) {
            $this->_isProductComposite = $this->_qty->getForm()->getDataObject()->isComposite();
        }
        return $this->_isProductComposite;
    }

    /**
     * Disable fields depending on product type
     *
     * @return $this
     */
    protected function _disableFields()
    {
        if ($this->getDisabled() || $this->_isProductComposite()) {
            $this->_qty->setDisabled('disabled');
        }
        if (!$this->_isProductComposite() && $this->_qty->getValue() === null) {
            $this->setDisabled('disabled');
        }
        if ($this->isLocked()) {
            $this->_qty->lock();
        }
        return $this;
    }

    /**
     * Get js for quantity and in stock synchronisation
     *
     * @param string $quantityFieldId
     * @param string $inStockFieldId
     * @return string
     */
    protected function _getJs($quantityFieldId, $inStockFieldId)
    {
        $isNewProduct = (int)$this->coreRegistry->registry('product')->isObjectNew();
        return "
            <script type='text/javascript'>
                require(['jquery', 'prototype', 'domReady!'], function($) {
                        var qty = $('#{$quantityFieldId}'),
                            productType = $('#product_type_id').val(),
                            stockAvailabilityField = $('#{$inStockFieldId}'),
                            manageStockField = $('#inventory_manage_stock'),
                            useConfigManageStockField = $('#inventory_use_config_manage_stock'),
                            fieldsAssociations = {
                                '{$quantityFieldId}': 'inventory_qty',
                                '{$inStockFieldId}': 'inventory_stock_availability'
                            };

                        var qtyDefaultValue = qty.val();
                        var disabler = function(event) {
                            if (typeof(event) === 'undefined') {
                                return;
                            }
                            var stockBeforeDisable = $.Event('stockbeforedisable', {productType: productType});
                            $('[data-tab-panel=product-details]').trigger(stockBeforeDisable);
                            if (stockBeforeDisable.result !== false) {
                                var manageStockValue = {$isNewProduct}
                                    ? (qty.val() === '' ? 0 : 1)
                                    : parseInt(manageStockField.val());
                                if ({$isNewProduct} && qtyDefaultValue !== null && qtyDefaultValue === qty.val()) {
                                    manageStockValue = parseInt(manageStockField.val());
                                } else {
                                    qtyDefaultValue = null;
                                }
                                var stockAssociations = $('#' + fieldsAssociations['{$inStockFieldId}']);
                                stockAvailabilityField.prop('disabled', !manageStockValue);
                                stockAssociations.prop('disabled', !manageStockValue);
                                if ($(event.currentTarget).attr('id') === qty.attr('id') && event.type != 'change') {
                                    stockAvailabilityField.val(manageStockValue);
                                    stockAssociations.val(manageStockValue);
                                }
                                if (parseInt(manageStockField.val()) != manageStockValue &&
                                    !(event && event.type == 'keyup')
                                ) {
                                    if (useConfigManageStockField.val() == 1) {
                                        useConfigManageStockField.prop('checked', false).val(0);
                                    }
                                    manageStockField.toggleClass('disabled', false).prop('disabled', false);
                                    manageStockField.val(manageStockValue);
                                }
                            }
                        };

                        //Fill corresponding field
                        var filler = function() {
                            var id = $(this).attr('id');
                            if ('undefined' !== typeof fieldsAssociations[id]) {
                                $('#' + fieldsAssociations[id]).val($(this).val());
                            } else {
                                $('#' + getKeyByValue(fieldsAssociations, id)).val($(this).val());
                            }

                            if (manageStockField.length) {
                                fireEvent(manageStockField.get(0), 'change');
                            }
                        };
                        //Get key by value from object
                        var getKeyByValue = function(object, value) {
                            var returnVal = false;
                            $.each(object, function(objKey, objValue) {
                                if (value === objValue) {
                                    returnVal = objKey;
                                }
                            });
                            return returnVal;
                        };
                        $.each(fieldsAssociations, function(generalTabField, advancedTabField) {
                            $('#' + generalTabField + ', #' + advancedTabField)
                                .on('focus blur change keyup click', filler)
                                .on('keyup change blur', disabler)
                                .trigger('change');
                        });

                })
            </script>
        ";
    }
}
