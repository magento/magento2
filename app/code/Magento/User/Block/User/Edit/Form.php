<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Block\User\Edit;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\Form as FormData;

/**
 * Adminhtml permissions user edit form
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Form extends Generic
{
    /**
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var FormData $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
