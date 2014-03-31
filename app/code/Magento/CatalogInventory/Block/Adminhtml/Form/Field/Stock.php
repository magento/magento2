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
 * @category    Magento
 * @package     Magento_CatalogInventory
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * HTML select element block
 */
namespace Magento\CatalogInventory\Block\Adminhtml\Form\Field;

use Magento\Data\Form;

class Stock extends \Magento\Data\Form\Element\Select
{
    const QUANTITY_FIELD_HTML_ID = 'qty';

    /**
     * Quantity field element
     *
     * @var \Magento\Data\Form\Element\Text
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
     * @var \Magento\Data\Form\Element\TextFactory
     */
    protected $_factoryText;

    /**
     * @param \Magento\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Escaper $escaper
     * @param \Magento\Data\Form\Element\TextFactory $factoryText
     * @param array $data
     */
    public function __construct(
        \Magento\Data\Form\Element\Factory $factoryElement,
        \Magento\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Escaper $escaper,
        \Magento\Data\Form\Element\TextFactory $factoryText,
        array $data = array()
    ) {
        $this->_factoryText = $factoryText;
        $this->_qty = isset($data['qty']) ? $data['qty'] : $this->_createQtyElement();
        unset($data['qty']);
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->setName($data['name']);
    }

    /**
     * Create quantity field
     *
     * @return \Magento\Data\Form\Element\Text
     */
    protected function _createQtyElement()
    {
        /** @var \Magento\Data\Form\Element\Text $element */
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
        // @codingStandardsIgnoreStart
        return "\n            <script>\n                jQuery(function(\$) {\n                    var qty = \$('#{$quantityFieldId}'),\n                        productType = \$('#product_type_id').val(),\n                        stockAvailabilityField = \$('#{$inStockFieldId}'),\n                        manageStockField = \$('#inventory_manage_stock'),\n                        useConfigManageStockField = \$('#inventory_use_config_manage_stock');\n\n                    var disabler = function(event) {\n                        var stockBeforeDisable = \$.Event('stockbeforedisable', {productType: productType});\n                        \$('#product_info_tabs_product-details_content').trigger(stockBeforeDisable);\n                        if (stockBeforeDisable.result !== false) {\n                            var manageStockValue = (qty.val() === '') ? 0 : 1;\n                            stockAvailabilityField.prop('disabled', !manageStockValue);\n                            if (manageStockField.val() != manageStockValue && !(event && event.type == 'keyup')) {\n                                if (useConfigManageStockField.val() == 1) {\n                                    useConfigManageStockField.removeAttr('checked').val(0);\n                                }\n                                manageStockField.toggleClass('disabled', false).prop('disabled', false);\n                                manageStockField.val(manageStockValue);\n                            }\n                        }\n                    };\n\n                    //Associated fields\n                    var fieldsAssociations = {\n                        '{$quantityFieldId}' : 'inventory_qty',\n                        '{$inStockFieldId}'  : 'inventory_stock_availability'\n                    };\n                    //Fill corresponding field\n                    var filler = function() {\n                        var id = \$(this).attr('id');\n                        if ('undefined' !== typeof fieldsAssociations[id]) {\n                            \$('#' + fieldsAssociations[id]).val(\$(this).val());\n                        } else {\n                            \$('#' + getKeyByValue(fieldsAssociations, id)).val(\$(this).val());\n                        }\n\n                        if (\$('#inventory_manage_stock').length) {\n                            fireEvent(\$('#inventory_manage_stock').get(0), 'change');\n                        }\n                    };\n                    //Get key by value from object\n                    var getKeyByValue = function(object, value) {\n                        var returnVal = false;\n                        \$.each(object, function(objKey, objValue){\n                            if (value === objValue) {\n                                returnVal = objKey;\n                            }\n                        });\n                        return returnVal;\n                    };\n                    \$.each(fieldsAssociations, function(generalTabField, advancedTabField) {\n                        \$('#' + generalTabField + ', #' + advancedTabField)\n                            .bind('focus blur change keyup click', filler)\n                            .bind('keyup change blur', disabler);\n                        filler.call(\$('#' + generalTabField));\n                        filler.call(\$('#' + advancedTabField));\n                    });\n                    disabler();\n                });\n            </script>\n        ";
        // @codingStandardsIgnoreEnd
    }
}
