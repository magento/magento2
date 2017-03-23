<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Controller\Adminhtml;

abstract class Indexer extends \Magento\Backend\App\Action
{
    /**
     * Check ACL permissions
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        switch ($this->_request->getActionName()) {
            case 'list':
                return $this->_authorization->isAllowed('Magento_Indexer::index');
            case 'massOnTheFly':
            case 'massChangelog':
                return $this->_authorization->isAllowed('Magento_Indexer::changeMode');
        }
        return false;
    }
}
