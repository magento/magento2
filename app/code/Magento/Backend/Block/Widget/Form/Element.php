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
     * @var \Magento\Framework\Object
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
     * @param \Magento\Framework\Object $formBlock
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
