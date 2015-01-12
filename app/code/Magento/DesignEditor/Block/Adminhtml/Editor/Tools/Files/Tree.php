<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Block class for rendering design editor tree of files
 */
namespace Magento\DesignEditor\Block\Adminhtml\Editor\Tools\Files;

class Tree extends \Magento\Theme\Block\Adminhtml\Wysiwyg\Files\Tree
{
    /**
     * Override root node name of tree specific to design editor.
     *
     * @return string
     */
    public function getRootNodeName()
    {
        return __('CSS Editor ') . __($this->_storageHelper->getStorageTypeName());
    }
}
