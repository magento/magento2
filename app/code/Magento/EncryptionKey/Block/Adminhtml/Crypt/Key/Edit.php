<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\EncryptionKey\Block\Adminhtml\Crypt\Key;

/**
 * Encryption key change edit page block
 *
 * @api
 * @deprecated
 * @see  Extensible Data ReEncryption Mechanism Implemented
 * @since 100.0.2
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Block module name
     *
     * @var string|null
     */
    protected $_blockGroup = null;

    /**
     * Controller name
     *
     * @var string
     */
    protected $_controller = 'crypt_key';

    /**
     * Instantiate save button
     *
     * @return void
     */
    protected function _construct()
    {
        \Magento\Framework\DataObject::__construct();
        $this->buttonList->add(
            'save',
            [
                'label' => __('Change Encryption Key'),
                'class' => 'save primary save-encryption-key',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'save', 'target' => '#edit_form']],
                ]
            ],
            1
        );
    }

    /**
     * Header text getter
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Encryption Key');
    }
}
