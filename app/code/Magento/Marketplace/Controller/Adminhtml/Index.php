<?php
/**
 * Product controller.
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Marketplace\Controller\Adminhtml;

abstract class Index extends \Magento\Backend\App\Action
{
    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Marketplace::index');
    }
}
