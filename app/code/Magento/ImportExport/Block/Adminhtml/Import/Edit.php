<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Import edit block
 */
namespace Magento\ImportExport\Block\Adminhtml\Import;

/**
 * @api
 * @since 100.0.2
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Internal constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->buttonList->remove('back');
        $this->buttonList->remove('reset');
        $this->buttonList->update('save', 'label', __('Check Data'));
        $this->buttonList->update('save', 'id', 'upload_button');
        $this->buttonList->update('save', 'onclick', 'varienImport.postToFrame();');
        $this->buttonList->update('save', 'data_attribute', '');

        $this->_objectId = 'import_id';
        $this->_blockGroup = 'Magento_ImportExport';
        $this->_controller = 'adminhtml_import';
    }

    /**
     * Get header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Import');
    }
}
