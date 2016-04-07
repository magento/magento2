<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Form;

use Magento\Framework\Data\Form;

/**
 * Form element widget block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
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
     * @param string $element
     * @return $this
     */
    public function setElement($element)
    {
        $this->_element = $element;
        return $this;
    }

    /**
     * @param Form $form
     * @return $this
     */
    public function setForm($form)
    {
        $this->_form = $form;
        return $this;
    }

    /**
     * @param \Magento\Framework\DataObject $formBlock
     * @return $this
     */
    public function setFormBlock($formBlock)
    {
        $this->_formBlock = $formBlock;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeToHtml()
    {
        $this->assign('form', $this->_form);
        $this->assign('element', $this->_element);
        $this->assign('formBlock', $this->_formBlock);

        return parent::_beforeToHtml();
    }
}
