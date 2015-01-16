<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Newsletter queue controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Newsletter\Controller\Adminhtml;

class Queue extends \Magento\Backend\App\Action
{
    /**
     * Check if user has enough privileges
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Newsletter::queue');
    }
}
