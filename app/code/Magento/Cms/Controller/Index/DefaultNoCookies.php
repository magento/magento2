<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Controller\Index;

class DefaultNoCookies extends \Magento\Framework\App\Action\Action
{
    /**
     * Default no cookies page action
     * Used if no cookies page don't configure or available
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
