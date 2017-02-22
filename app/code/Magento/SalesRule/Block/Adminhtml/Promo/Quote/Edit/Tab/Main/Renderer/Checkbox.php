<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Main\Renderer;

/**
 * Renderer for specific checkbox that is used on Rule Information tab in Cart Price Rules
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Checkbox extends \Magento\Backend\Block\AbstractBlock implements
    \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     */
    protected $_elementFactory;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Data\Form\Element\Factory $elementFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Data\Form\Element\Factory $elementFactory,
        array $data = []
    ) {
        $this->_elementFactory = $elementFactory;
        parent::__construct($context, $data);
    }

    /**
     * Checkbox render function
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        /** @var \Magento\Framework\Data\Form\Element\Checkbox $checkbox */
        $checkbox = $this->_elementFactory->create('checkbox', ['data' => $element->getData()]);
        $checkbox->setForm($element->getForm());

        $elementHtml = sprintf(
            '<div class="field no-label field-%s with-note">' .
            '<div class="control">' .
            '<div class="nested">' .
            '<div class="field choice"> %s' .
            '<label class="label" for="%s">%s</label>' .
            '<p class="note">%s</p>' .
            '</div>' .
            '</div>' .
            '</div>' .
            '</div>',
            $element->getHtmlId(),
            $checkbox->getElementHtml(),
            $element->getHtmlId(),
            $element->getLabel(),
            $element->getNote()
        );
        return $elementHtml;
    }
}
