<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
