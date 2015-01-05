<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Customer\Controller\Address;

class NewAction extends \Magento\Customer\Controller\Address
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_forward('form');
    }
}
