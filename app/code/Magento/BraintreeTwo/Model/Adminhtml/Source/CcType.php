<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Model\Adminhtml\Source;

use Magento\Payment\Model\Source\Cctype as PaymentCcType;

/**
 * Class CcType
 * @codeCoverageIgnore
 */
class CcType extends PaymentCctype
{
    /**
     * Allowed credit card types
     *
     * @return string[]
     */
    public function getAllowedTypes()
    {
        return ['VI', 'MC', 'AE', 'DI', 'JCB', 'OT'];
    }

    /**
     * Geting credit cards types
     *
     * @return array
     */
    public function getCcTypes()
    {
        return $this->_paymentConfig->getCcTypes();
    }
}
