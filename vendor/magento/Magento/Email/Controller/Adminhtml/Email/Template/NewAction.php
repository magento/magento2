<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Email\Controller\Adminhtml\Email\Template;

class NewAction extends \Magento\Email\Controller\Adminhtml\Email\Template
{
    /**
     * New transactional email action
     *
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
