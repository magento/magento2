<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Customer\Controller\Adminhtml\Group;

class Edit extends \Magento\Customer\Controller\Adminhtml\Group
{
    /**
     * Edit customer group action. Forward to new action.
     *
     * @return void
     */
    public function execute()
    {
        $this->_forward('new');
    }
}
