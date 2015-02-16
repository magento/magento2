<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rss\Controller\Index;

use Magento\Framework\App\Action\NotFoundException;

class Index extends \Magento\Rss\Controller\Index
{
    /**
     * Index action
     *
     * @return void
     * @throws NotFoundException
     */
    public function execute()
    {
        if ($this->_scopeConfig->getValue('rss/config/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $this->_view->loadLayout();
            $this->_view->renderLayout();
        } else {
            throw new NotFoundException();
        }
    }
}
