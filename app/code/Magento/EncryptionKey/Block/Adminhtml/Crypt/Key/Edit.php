<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\EncryptionKey\Block\Adminhtml\Crypt\Key;

/**
 * Encryption key change edit page block
 *
 * @api
 * @since 2.0.0
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Block module name
     *
     * @var string|null
     * @since 2.0.0
     */
    protected $_blockGroup = null;

    /**
     * Controller name
     *
     * @var string
     * @since 2.0.0
     */
    protected $_controller = 'crypt_key';

    /**
     * Instantiate save button
     *
     * @return void
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getHeaderText()
    {
        return __('Encryption Key');
    }
}
