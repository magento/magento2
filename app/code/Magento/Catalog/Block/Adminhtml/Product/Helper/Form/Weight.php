<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Adminhtml\Product\Helper\Form;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Directory\Helper\Data;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form;
use Magento\Catalog\Model\Product\Edit\WeightResolver;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Radios;
use Magento\Framework\Data\Form\Element\Text;
use Magento\Framework\Escaper;
use Magento\Framework\Locale\Format;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

/**
 * Product form weight field helper
 */
class Weight extends Text
{
    /**
     * Weight switcher radio-button element
     *
     * @var Radios
     */
    protected $weightSwitcher;

    /**
     * @var Format
     */
    protected $localeFormat;

    /**
     * @var Data
     */
    protected $directoryHelper;

    /**
     * @var SecureHtmlRenderer
     */
    private $secureRenderer;

    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param Format $localeFormat
     * @param Data $directoryHelper
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        Format $localeFormat,
        Data $directoryHelper,
        array $data = [],
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        $this->directoryHelper = $directoryHelper;
        $this->localeFormat = $localeFormat;
        $this->weightSwitcher = $factoryElement->create('radios');
        $this->weightSwitcher->setValue(
            WeightResolver::HAS_NO_WEIGHT
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
        $this->secureRenderer = $secureRenderer ?? ObjectManager::getInstance()->get(SecureHtmlRenderer::class);
    }

    /**
     * Add Weight Switcher radio-button element html to weight field
     *
     * @return string
     */
    public function getElementHtml()
    {
        if ($this->getForm()->getDataObject()->getTypeInstance()->hasWeight()) {
            $this->weightSwitcher->setValue(WeightResolver::HAS_WEIGHT);
        }

        if ($this->getDisabled()) {
            $this->weightSwitcher->setDisabled($this->getDisabled());
        }

        $htmlId = $this->getHtmlId();
        $html = '';

        if ($beforeElementHtml = $this->getBeforeElementHtml()) {
            $html .= '<label class="addbefore" for="' . $htmlId . '">' . $beforeElementHtml . '</label>';
        }

        $html .= '<div class="admin__control-addon">';

        if (is_array($this->getValue())) {
            foreach ($this->getValue() as $value) {
                $html .= $this->getHtmlForInputByValue($this->_escape($value));
            }
        } else {
            $html .= $this->getHtmlForInputByValue($this->getEscapedValue());
        }

        $html .= '<label class="admin__addon-suffix" for="' .
            $this->getHtmlId() .
            '"><span>' .
            $this->_escaper->escapeHtml($this->directoryHelper->getWeightUnit()) .
            '</span></label></div>';

        if ($afterElementJs = $this->getAfterElementJs()) {
            $html .= $afterElementJs;
        }

        if ($afterElementHtml = $this->getAfterElementHtml()) {
            $html .= '<label class="addafter" for="' . $htmlId . '">' . $afterElementHtml . '</label>';
        }

        $html .= $this->getHtmlForWeightSwitcher();

        return $html;
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
     * @inheritDoc
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

    /**
     * Get input html by sting value.
     *
     * @param string|null $value
     *
     * @return string
     */
    private function getHtmlForInputByValue($value)
    {
        return '<input id="' . $this->getHtmlId() . '" name="' . $this->getName() . '" ' . $this->_getUiId()
            . ' value="' . $value . '" ' . $this->serialize($this->getHtmlAttributes()) . '/>';
    }

    /**
     * Get weight switcher html.
     *
     * @return string
     */
    private function getHtmlForWeightSwitcher()
    {
        $html = '<div class="admin__control-addon">';
        $html .= '<div class="admin__field-control weight-switcher">' .
            '<div class="admin__control-switcher" data-role="weight-switcher">' .
            $this->weightSwitcher->getLabelHtml() .
            '<div class="admin__field-control-group">' .
            $this->weightSwitcher->getElementHtml() .
            '</div>' .
            '</div>';

        $html .= '<label class="addafter">';
        $elementId = ProductAttributeInterface::CODE_HAS_WEIGHT;
        $nameAttributeHtml = 'name="' . $elementId . '_checkbox"';
        $dataCheckboxName = "toggle_{$elementId}";
        $checkboxLabel = __('Change');
        $html .= <<<HTML
<span class="attribute-change-checkbox">
    <input type="checkbox" id="$dataCheckboxName" name="$dataCheckboxName" class="checkbox" $nameAttributeHtml/>
    <label class="label" for="$dataCheckboxName">
        {$checkboxLabel}
    </label>
</span>
HTML;

        $html .= '</label></div></div>';

        $html .= /* @noEscape */ $this->secureRenderer->renderEventListenerAsTag(
            'onclick',
            "toogleFieldEditMode(this, 'weight-switcher1'); toogleFieldEditMode(this, 'weight-switcher0');",
            "#". $dataCheckboxName
        );

        return $html;
    }
}
