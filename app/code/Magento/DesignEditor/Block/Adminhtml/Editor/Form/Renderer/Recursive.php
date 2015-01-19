<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Block\Adminhtml\Editor\Form\Renderer;

/**
 * Recursive renderer that uses several templates
 *
 * @method string getHtml()
 * @method \Magento\DesignEditor\Block\Adminhtml\Editor\Form\Renderer\Recursive setHtml($html)
 */
class Recursive extends \Magento\Backend\Block\Template implements
    \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * Form element to render
     *
     * @var \Magento\Framework\Data\Form\Element\AbstractElement
     */
    protected $_element;

    /**
     * Path to template file in theme.
     *
     * Recursive renderer use '_template' property for rendering templates one by one
     *
     * @var string
     */
    protected $_template = null;

    /**
     * Set of templates to render
     *
     * Upper is rendered first and is inserted into next using <?php echo $this->getHtml() ?>
     *
     * @var string[]
     */
    protected $_templates = [];

    /**
     * Get element renderer bound to
     *
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function getElement()
    {
        return $this->_element;
    }

    /**
     * Render form element as HTML
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->_element = $element;

        foreach ($this->_templates as $template) {
            $this->setTemplate($template);
            $this->setHtml($this->toHtml());
        }

        return $this->getHtml();
    }
}
