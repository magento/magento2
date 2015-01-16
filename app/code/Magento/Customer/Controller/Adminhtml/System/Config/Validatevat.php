<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\System\Config;

/**
 * VAT validation controller
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Validatevat extends \Magento\Backend\App\Action
{
    /**
     * Perform customer VAT ID validation
     *
     * @return \Magento\Framework\Object
     */
    protected function _validate()
    {
        return $this->_objectManager->get(
            'Magento\Customer\Model\Vat'
        )->checkVatNumber(
            $this->getRequest()->getParam('country'),
            $this->getRequest()->getParam('vat')
        );
    }
}
