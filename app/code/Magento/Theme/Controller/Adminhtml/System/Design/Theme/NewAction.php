<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design\Theme;

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
