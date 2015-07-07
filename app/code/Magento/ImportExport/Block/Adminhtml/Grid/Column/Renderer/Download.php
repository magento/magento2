<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Block\Adminhtml\Grid\Column\Renderer;

use Magento\ImportExport\Model\Import;

/**
 * Backup grid item renderer
 */
class Download extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    /**
     * Renders grid column
     *
     * @param \Magento\Framework\Object $row
     * @return mixed
     */
    public function _getValue(\Magento\Framework\Object $row)
    {
        return '<p> ' . $row->getData('imported_file') .  '</p><a href="'
        . $this->getUrl('*/*/download', ['filename' => $row->getData('imported_file')]) . '">Download</a>';
    }
}
