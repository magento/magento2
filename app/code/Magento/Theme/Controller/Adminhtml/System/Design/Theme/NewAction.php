<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design\Theme;

/**
 * Class NewAction
 * @deprecated 100.2.0
 */
class NewAction extends \Magento\Theme\Controller\Adminhtml\System\Design\Theme
{
    /**
     * Create new theme
     *
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
