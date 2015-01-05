<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Widget Instance edit form
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Widget\Block\Adminhtml\Widget\Instance\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
