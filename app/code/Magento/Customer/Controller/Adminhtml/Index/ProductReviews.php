<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\Customer\Controller\RegistryConstants;

class ProductReviews extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Get customer's product reviews list
     *
     * @return void
     */
    public function execute()
    {
        $this->_initCustomer();
        $resultPage = $this->resultPageFactory->create();
        $this->prepareDefaultCustomerTitle($resultPage);
        $resultPage->getLayout()->getBlock('admin.customer.reviews')->setCustomerId(
            $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID)
        )
            ->setUseAjax(true);
        return $resultPage;
    }
}
