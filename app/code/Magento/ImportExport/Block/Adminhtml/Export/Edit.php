<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Export edit block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\ImportExport\Block\Adminhtml\Export;

/**
 * @api
 * @since 2.0.0
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Internal constructor
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();
        $this->removeButton('back')->removeButton('reset')->removeButton('save');

        $this->_objectId = 'export_id';
        $this->_blockGroup = 'Magento_ImportExport';
        $this->_controller = 'adminhtml_export';
    }

    /**
     * Get header text
     *
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function getHeaderText()
    {
        return __('Export');
    }
}
