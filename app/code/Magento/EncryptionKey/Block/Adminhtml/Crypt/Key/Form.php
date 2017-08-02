<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\EncryptionKey\Block\Adminhtml\Crypt\Key;

/**
 * Encryption key change form block
 *
 * @api
 * @since 2.0.0
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Add form fields
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getData('action'),
                    'method' => 'post'
                ]
            ]
        );
        $fieldset = $form->addFieldset('main_fieldset', ['legend' => __('New Encryption Key')]);
        $fieldset->addField(
            'enc_key_note',
            'note',
            ['text' => __('The encryption key is used to protect passwords and other sensitive data.')]
        );
        $fieldset->addField(
            'generate_random',
            'select',
            [
                'name' => 'generate_random',
                'label' => __('Auto-generate a Key'),
                'options' => [0 => __('No'), 1 => __('Yes')],
                'onclick' => "var cryptKey = jQuery('#crypt_key'); var cryptKeyBlock = cryptKey.parent().parent(); ".
                    "cryptKey.prop('disabled', this.value === '1'); " .
                    "if (cryptKey.prop('disabled')) { cryptKeyBlock.hide() } " .
                    "else { cryptKeyBlock.show() }",
                'note' => __('The generated key will be displayed after changing.')
            ]
        );
        $fieldset->addField(
            'crypt_key',
            'text',
            ['name' => 'crypt_key', 'label' => __('New Key'), 'style' => 'width:32em;', 'maxlength' => 32]
        );
        $form->setUseContainer(true);
        if ($data = $this->getFormData()) {
            $form->addValues($data);
        }
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
