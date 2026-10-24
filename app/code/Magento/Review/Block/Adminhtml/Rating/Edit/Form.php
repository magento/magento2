<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Review\Block\Adminhtml\Rating\Edit;

/**
 * Rating edit form block
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Prepare the form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl('review/rating/save', ['id' => $this->getRequest()->getParam('id')]),
                    'method' => 'post',
                ],
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
