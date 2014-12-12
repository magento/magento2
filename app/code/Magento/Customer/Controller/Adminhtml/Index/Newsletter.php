<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\Customer\Controller\RegistryConstants;

class Newsletter extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Customer newsletter grid
     *
     * @return void
     */
    public function execute()
    {
        $this->_initCustomer();
        $customerId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        /** @var  \Magento\Newsletter\Model\Subscriber $subscriber */
        $subscriber = $this->_objectManager
            ->create('Magento\Newsletter\Model\Subscriber')
            ->loadByCustomerId($customerId);

        $this->_coreRegistry->register('subscriber', $subscriber);
        $this->_view->loadLayout();
        $this->prepareDefaultCustomerTitle();
        $this->_view->renderLayout();
    }
}
