<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Dashboard admin controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Backend\Controller\Adminhtml;

class Dashboard extends \Magento\Backend\App\Action
{
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Adminhtml::dashboard');
    }
}
