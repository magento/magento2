<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product form weight field helper
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Helper\Form;

use Magento\Framework\Data\Form;
use Magento\Catalog\Model\Product\Edit\WeightResolver;

class Weight extends \Magento\Framework\Data\Form\Element\Text
{
    /**
     * Weight switcher radio-button element
     *
     * @var \Magento\Framework\Data\Form\Element\Checkbox
     */
    protected $weightSwitcher;

    /**
     * @var \Magento\Framework\Locale\Format
     */
    protected $localeFormat;

    /**
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\Locale\Format $localeFormat
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Locale\Format $localeFormat,
        array $data = []
    ) {
        $this->localeFormat = $localeFormat;
        $this->weightSwitcher = $factoryElement->create('radios');
        $this->weightSwitcher->setValue(
            WeightResolver::HAS_WEIGHT
        )->setValues(
            [
                ['value' => WeightResolver::HAS_WEIGHT, 'label' => __('Yes')],
                ['value' => WeightResolver::HAS_NO_WEIGHT, 'label' => __('No')]
            ]
        )->setId(
            'weight-switcher'
        )->setName(
            'product_has_weight'
        )->setLabel(
            __('Does this have a weight?')
        );
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->addClass('validate-zero-or-greater');
    }

    /**
     * Add Weight Switcher radio-button element html to weight field
     *
     * @return string
     */
    public function getElementHtml()
    {
        if (!$this->getForm()->getDataObject()->getTypeInstance()->hasWeight()) {
            $this->weightSwitcher->setValue(WeightResolver::HAS_NO_WEIGHT);
        }
        if ($this->getDisabled()) {
            $this->weightSwitcher->setDisabled($this->getDisabled());
        }
        return '<div class="admin__fieldset weight-switcher" data-role="weight-switcher">' .
            '<div class="admin__field-control">' .
                $this->weightSwitcher->getLabelHtml() .
                '<div class="admin__field-control-group">' .
                    $this->weightSwitcher->getElementHtml() .
                '</div>' .
                '<div class="admin__control-addon">' .
                parent::getElementHtml() .
                    '<label class="admin__addon-suffix" for="' .
                    $this->getHtmlId() .
                    '"><span>' .
                    __('lbs') .
                    '</span></label>' .
                '</div>' .
            '</div>' .
        '</div>';
    }

    /**
     * Set form for both fields
     *
     * @param Form $form
     * @return $this
     */
    public function setForm($form)
    {
        $this->weightSwitcher->setForm($form);
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
