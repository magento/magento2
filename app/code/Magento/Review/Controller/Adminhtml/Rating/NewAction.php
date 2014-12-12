<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Review\Controller\Adminhtml\Rating;

class NewAction extends \Magento\Review\Controller\Adminhtml\Rating
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
