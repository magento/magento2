<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Fieldset config form element renderer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Frontend\Product;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class \Magento\Catalog\Block\Adminhtml\Product\Frontend\Product\Watermark
 *
 * @since 2.0.0
 */
class Watermark extends \Magento\Backend\Block\AbstractBlock implements
    \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     * @since 2.0.0
     */
    protected $_elementFactory;

    /**
     * @var \Magento\Config\Block\System\Config\Form\Field
     * @since 2.0.0
     */
    protected $_formField;

    /**
     * @var \Magento\Catalog\Model\Config\Source\Watermark\Position
     * @since 2.0.0
     */
    protected $_watermarkPosition;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $_imageTypes;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Catalog\Model\Config\Source\Watermark\Position $watermarkPosition
     * @param \Magento\Config\Block\System\Config\Form\Field $formField
     * @param \Magento\Framework\Data\Form\Element\Factory $elementFactory
     * @param array $imageTypes
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Catalog\Model\Config\Source\Watermark\Position $watermarkPosition,
        \Magento\Config\Block\System\Config\Form\Field $formField,
        \Magento\Framework\Data\Form\Element\Factory $elementFactory,
        array $imageTypes = [],
        array $data = []
    ) {
        $this->_watermarkPosition = $watermarkPosition;
        $this->_formField = $formField;
        $this->_elementFactory = $elementFactory;
        $this->_imageTypes = $imageTypes;
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     * @since 2.0.0
     */
    public function render(AbstractElement $element)
    {
        $html = $this->_getHeaderHtml($element);
        foreach ($this->_imageTypes as $key => $attribute) {
            /**
             * Watermark size field
             */
            /** @var \Magento\Framework\Data\Form\Element\Text $field */
            $field = $this->_elementFactory->create('text');
            $field->setName(
                "groups[watermark][fields][{$key}_size][value]"
            )->setForm(
                $this->getForm()
            )->setLabel(
                __('Size for %1', __($attribute['title']))
            )->setRenderer(
                $this->_formField
            );
            $html .= $field->toHtml();

            /**
             * Watermark upload field
             */
            /** @var \Magento\Framework\Data\Form\Element\Imagefile $field */
            $field = $this->_elementFactory->create('imagefile');
            $field->setName(
                "groups[watermark][fields][{$key}_image][value]"
            )->setForm(
                $this->getForm()
            )->setLabel(
                __('Watermark File for %1', __($attribute['title']))
            )->setRenderer(
                $this->_formField
            );
            $html .= $field->toHtml();

            /**
             * Watermark position field
             */
            /** @var \Magento\Framework\Data\Form\Element\Select $field */
            $field = $this->_elementFactory->create('select');
            $field->setName(
                "groups[watermark][fields][{$key}_position][value]"
            )->setForm(
                $this->getForm()
            )->setLabel(
                __('Position of Watermark for %1', __($attribute['title']))
            )->setRenderer(
                $this->_formField
            )->setValues(
                $this->_watermarkPosition->toOptionArray()
            );
            $html .= $field->toHtml();
        }

        $html .= $this->_getFooterHtml($element);

        return $html;
    }

    /**
     * @param AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @since 2.0.0
     */
    protected function _getHeaderHtml($element)
    {
        $id = $element->getHtmlId();
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');

        $html = '<h4 class="icon-head head-edit-form">' . $element->getLegend() . '</h4>';
        $html .= '<fieldset class="config" id="' . $element->getHtmlId() . '">';
        $html .= '<legend>' . $element->getLegend() . '</legend>';

        // field label column
        $html .= '<table><colgroup class="label" /><colgroup class="value" />';
        if (!$default) {
            $html .= '<colgroup class="use-default" />';
        }
        $html .= '<tbody>';

        return $html;
    }

    /**
     * @param AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    protected function _getFooterHtml($element)
    {
        $html = '</tbody></table></fieldset>';
        return $html;
    }
}
