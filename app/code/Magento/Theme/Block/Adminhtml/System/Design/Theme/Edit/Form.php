<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit;

use Magento\Backend\Block\Widget\Form as WidgetForm;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\Form as FormData;
use Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Form as SystemDesignThemeEditForm;

/**
 * Theme Edit Form
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Form extends Generic
{
    /**
     * Initialize theme form
     *
     * @return SystemDesignThemeEditForm|WidgetForm
     */
    protected function _prepareForm()
    {
        /** @var FormData $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl('adminhtml/*/save'),
                    'enctype' => 'multipart/form-data',
                    'method' => 'post',
                ],
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
