<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rss\Controller\Index;

use Magento\Framework\Exception\NotFoundException;

/**
 * Class \Magento\Rss\Controller\Index\Index
 *
 */
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
            throw new NotFoundException(__('Page not found.'));
        }
    }
}
