<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Customer\Controller\Adminhtml\System\Config\Validatevat;

class Validate extends \Magento\Customer\Controller\Adminhtml\System\Config\Validatevat
{
    /**
     * Check whether vat is valid
     *
     * @return void
     */
    public function execute()
    {
        $result = $this->_validate();
        $this->getResponse()->setBody((int)$result->getIsValid());
    }
}
