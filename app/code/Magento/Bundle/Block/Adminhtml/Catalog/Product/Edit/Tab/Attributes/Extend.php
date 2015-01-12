<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Bundle Extended Attribures Block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Attributes;

class Extend extends \Magento\Catalog\Block\Adminhtml\Form\Renderer\Fieldset\Element
{
    const DYNAMIC = 0;

    const FIXED = 1;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setCanEditPrice(true);
        $this->setCanReadPrice(true);
    }

    /**
     * Get Element Html
     *
     * @return string
     */
    public function getElementHtml()
    {
        $elementHtml = parent::getElementHtml();

        $switchAttributeCode = $this->getAttribute()->getAttributeCode() . '_type';
        $switchAttributeValue = $this->getProduct()->getData($switchAttributeCode);

        $html = '<select name="product[' .
            $switchAttributeCode .
            ']" id="' .
            $switchAttributeCode .
            '" type="select" class="required-entry select next-toinput"' .
            ($this->getProduct()->getId() &&
            $this->getAttribute()->getAttributeCode() == 'price' ||
            $this->getElement()->getReadonly() ? ' disabled="disabled"' : '') . '>
            <option value="">' . __('-- Select --') . '</option>
            <option ' . ($switchAttributeValue ==
            self::DYNAMIC ? 'selected' : '') . ' value="' . self::DYNAMIC . '">' . __('Dynamic') . '</option>
            <option ' . ($switchAttributeValue ==
            self::FIXED ? 'selected' : '') . ' value="' . self::FIXED . '">' . __('Fixed') . '</option>
        </select>';

        if (!($this->getAttribute()->getAttributeCode() == 'price' && $this->getCanReadPrice() === false)) {
            $html = '<div class="' .
                $this->getAttribute()->getAttributeCode() .
                ' ">' .
                $elementHtml .
                '</div>' .
                $html;
        }
        if ($this->getDisableChild() && !$this->getElement()->getReadonly()) {
            $html .= "<script type=\"text/javascript\">
                function " .
                $switchAttributeCode .
                "_change() {
                    if ($('" .
                $switchAttributeCode .
                "').value == '" .
                self::DYNAMIC .
                "') {
                        if ($('" .
                $this->getAttribute()->getAttributeCode() .
                "')) {
                            $('" .
                $this->getAttribute()->getAttributeCode() .
                "').disabled = true;
                            $('" .
                $this->getAttribute()->getAttributeCode() .
                "').value = '';
                            $('" .
                $this->getAttribute()->getAttributeCode() .
                "').removeClassName('required-entry');
                        }

                        if ($('dynamic-price-warning')) {
                            $('dynamic-price-warning').show();
                        }
                    } else {
                        if ($('" .
                $this->getAttribute()->getAttributeCode() .
                "')) {";

            if ($this->getAttribute()->getAttributeCode() == 'price' &&
                $this->getCanEditPrice() === false &&
                $this->getCanReadPrice() === true &&
                $this->getProduct()->isObjectNew()
            ) {
                $defaultProductPrice = $this->getDefaultProductPrice() ? $this->getDefaultProductPrice() : "''";
                $html .= "$('" .
                    $this->getAttribute()->getAttributeCode() .
                    "').value = " .
                    $defaultProductPrice .
                    ";";
            } else {
                $html .= "$('" .
                    $this->getAttribute()->getAttributeCode() .
                    "').disabled = false;
                          $('" .
                    $this->getAttribute()->getAttributeCode() .
                    "').addClassName('required-entry');";
            }

            $html .= "}

                        if ($('dynamic-price-warning')) {
                            $('dynamic-price-warning').hide();
                        }
                    }
                }" . "\n";

            if (!($this->getAttribute()->getAttributeCode() == 'price' &&
                !$this->getCanEditPrice() &&
                !$this->getProduct()->isObjectNew())
            ) {
                $html .= "$('" . $switchAttributeCode . "').observe('change', " . $switchAttributeCode . "_change);";
            }
            $html .= $switchAttributeCode . "_change();
            </script>";
        }
        return $html;
    }

    /**
     * @return mixed
     */
    public function getProduct()
    {
        if (!$this->getData('product')) {
            $this->setData('product', $this->_coreRegistry->registry('product'));
        }
        return $this->getData('product');
    }
}
