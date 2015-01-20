<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
