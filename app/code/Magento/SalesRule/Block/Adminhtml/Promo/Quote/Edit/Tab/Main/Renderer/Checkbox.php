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
namespace Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Main\Renderer;

/**
 * Renderer for specific checkbox that is used on Rule Information tab in Shopping cart price rules
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
        array $data = array()
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
        $checkbox = $this->_elementFactory->create('checkbox', array('data' => $element->getData()));
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
