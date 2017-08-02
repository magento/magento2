<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Admin form widget
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Newsletter\Block\Adminhtml\Template\Preview;

/**
 * @api
 * @since 2.0.0
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Preparing from for revision page
     *
     * @return \Magento\Backend\Block\Widget\Form
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @since 2.0.0
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'preview_form',
                    'action' => $this->getUrl('*/*/drop', ['_current' => true]),
                    'method' => 'post',
                ],
            ]
        );

        if ($data = $this->getFormData()) {
            $mapper = ['preview_store_id' => 'store_id'];

            if (empty($data['id']) && !empty($data['text'])) {
                $this->_backendSession->setPreviewData($data);
            }

            foreach ($data as $key => $value) {
                if (array_key_exists($key, $mapper)) {
                    $name = $mapper[$key];
                } else {
                    $name = $key;
                }
                $form->addField($key, 'hidden', ['name' => $name]);
            }
            $form->setValues($data);
        }

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
