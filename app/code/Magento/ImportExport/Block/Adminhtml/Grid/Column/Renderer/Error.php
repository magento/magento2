<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Block\Adminhtml\Grid\Column\Renderer;

use Magento\ImportExport\Model\Import;

/**
 * Backup grid item renderer
 */
class Error extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    /**
     * Renders grid column
     *
     * @param \Magento\Framework\DataObject $row
     * @return mixed
     */
    public function _getValue(\Magento\Framework\DataObject $row)
    {
        $result = '';
        if ($row->getData('error_file') != '') {
            $result = '<p> ' . $row->getData('error_file') .  '</p><a href="'
                . $this->getUrl('*/*/download', ['filename' => $row->getData('error_file')]) . '">'
                . __('Download')
                . '</a>';
        }
        return $result;
    }
}
