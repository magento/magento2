<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\System\Account;

/**
 * Adminhtml edit admin user account
 *
 * @api
 * @since 100.0.2
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Initialise the page
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_blockGroup = 'Magento_Backend';
        $this->_controller = 'system_account';
        $this->buttonList->update('save', 'label', __('Save Account'));
        $this->buttonList->remove('delete');
        $this->buttonList->remove('back');
    }

    /**
     * Return a Phrase for the header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('My Account');
    }
}
