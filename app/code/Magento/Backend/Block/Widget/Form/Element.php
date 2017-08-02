<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Form;

use Magento\Framework\Data\Form;

/**
 * Form element widget block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Element extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $_element;

    /**
     * @var Form
     * @since 2.0.0
     */
    protected $_form;

    /**
     * @var \Magento\Framework\DataObject
     * @since 2.0.0
     */
    protected $_formBlock;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'Magento_Backend::widget/form/element.phtml';

    /**
     * @param string $element
     * @return $this
     * @since 2.0.0
     */
    public function setElement($element)
    {
        $this->_element = $element;
        return $this;
    }

    /**
     * @param Form $form
     * @return $this
     * @since 2.0.0
     */
    public function setForm($form)
    {
        $this->_form = $form;
        return $this;
    }

    /**
     * @param \Magento\Framework\DataObject $formBlock
     * @return $this
     * @since 2.0.0
     */
    public function setFormBlock($formBlock)
    {
        $this->_formBlock = $formBlock;
        return $this;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function _beforeToHtml()
    {
        $this->assign('form', $this->_form);
        $this->assign('element', $this->_element);
        $this->assign('formBlock', $this->_formBlock);

        return parent::_beforeToHtml();
    }
}
