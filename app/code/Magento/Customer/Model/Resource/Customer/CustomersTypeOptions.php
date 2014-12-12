<?php
/**
 * Customer type option array
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Customer\Model\Resource\Customer;

class CustomersTypeOptions implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Return statuses option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            \Magento\Customer\Model\Visitor::VISITOR_TYPE_CUSTOMER => __('Customer'),
            \Magento\Customer\Model\Visitor::VISITOR_TYPE_VISITOR => __('Visitor')
        ];
    }
}
