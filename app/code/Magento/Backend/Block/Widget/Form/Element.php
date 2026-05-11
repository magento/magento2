<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Form;

use Magento\Framework\Data\Form;

/**
 * Form element widget block
 */
class Element extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_element;

    /**
     * @var Form
     */
    protected $_form;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_formBlock;

    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::widget/form/element.phtml';

    /**
     * Set element and return self
     *
     * @param string $element
     * @return $this
     */
    public function setElement($element)
    {
        $this->_element = $element;
        return $this;
    }

    /**
     * Set form and return self
     *
     * @param Form $form
     * @return $this
     */
    public function setForm($form)
    {
        $this->_form = $form;
        return $this;
    }

    /**
     * Set form block and return self
     *
     * @param \Magento\Framework\DataObject $formBlock
     * @return $this
     */
    public function setFormBlock($formBlock)
    {
        $this->_formBlock = $formBlock;
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function _beforeToHtml()
    {
        $this->assign('form', $this->_form);
        $this->assign('element', $this->_element);
        $this->assign('formBlock', $this->_formBlock);

        return parent::_beforeToHtml();
    }
}
