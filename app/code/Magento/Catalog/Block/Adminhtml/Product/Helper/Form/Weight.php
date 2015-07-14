<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product form weight field helper
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Helper\Form;

use Magento\Framework\Data\Form;

class Weight extends \Magento\Framework\Data\Form\Element\Text
{
    const VIRTUAL_FIELD_HTML_ID = 'weight_and_type_switcher';

    /**
     * Is virtual checkbox element
     *
     * @var \Magento\Framework\Data\Form\Element\Checkbox
     */
    protected $_virtual;

    /**
     * Catalog helper
     *
     * @var \Magento\Catalog\Helper\Product
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\Locale\Format
     */
    protected $localeFormat;

    /**
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Catalog\Helper\Product $helper
     * @param \Magento\Framework\Locale\Format $localeFormat
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        \Magento\Catalog\Helper\Product $helper,
        \Magento\Framework\Locale\Format $localeFormat,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->localeFormat = $localeFormat;
        $this->_virtual = $factoryElement->create('checkbox');
        $this->_virtual->setId(
            self::VIRTUAL_FIELD_HTML_ID
        )->setName(
            'is_virtual'
        )->setLabel(
            $this->_helper->getTypeSwitcherControlLabel()
        );
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->addClass('validate-zero-or-greater');
    }

    /**
     * Add Is Virtual checkbox html to weight field
     *
     * @return string
     */
    public function getElementHtml()
    {
        if (!$this->getForm()->getDataObject()->getTypeInstance()->hasWeight()) {
            $this->_virtual->setChecked('checked');
        }
        if ($this->getDisabled()) {
            $this->_virtual->setDisabled($this->getDisabled());
        }
        return '<div class="fields-group-2"><div class="field"><div class="addon"><div class="control">' .
            parent::getElementHtml() .
            '<label class="addafter" for="' .
            $this->getHtmlId() .
            '"><strong>' .
            __('lbs') .
            '</strong></label>' .
            '</div></div></div><div class="field choice">' .
            $this->_virtual->getElementHtml() .
            $this->_virtual->getLabelHtml() .
            '</div></div>';
    }

    /**
     * Set form for both fields
     *
     * @param Form $form
     * @return $this
     */
    public function setForm($form)
    {
        $this->_virtual->setForm($form);
        return parent::setForm($form);
    }

    /**
     * @param null|int|string $index
     * @return null|string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getEscapedValue($index = null)
    {
        $value = $this->getValue();

        if (!is_numeric($value)) {
            return null;
        }

        if ($this->getEntityAttribute()) {
            $format= $this->localeFormat->getPriceFormat();
            $value = number_format($value, $format['precision'], $format['decimalSymbol'], $format['groupSymbol']);
        } else {
            // default format:  1234.56
            $value = number_format($value, 2, null, '');
        }

        return $value;
    }
}
