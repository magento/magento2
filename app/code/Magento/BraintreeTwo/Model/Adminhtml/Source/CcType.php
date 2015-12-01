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
     * List of specific credit card types
     * @var array
     */
    private $mapper = [
        'CUP' => 'China Union Pay'
    ];

    /**
     * Allowed credit card types
     *
     * @return string[]
     */
    public function getAllowedTypes()
    {
        return ['VI', 'MC', 'AE', 'DI', 'JCB', 'MI', 'DN', 'CUP', 'OT'];
    }

    /**
     * Getting credit cards types
     *
     * @return array
     */
    public function getCcTypes()
    {
        return array_merge($this->mapper, $this->_paymentConfig->getCcTypes());
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $allowed = $this->getAllowedTypes();
        $options = [];

        foreach ($this->getCcTypes() as $code => $name) {
            if (in_array($code, $allowed)) {
                $options[] = ['value' => $code, 'label' => $name];
            }
        }

        return $options;
    }
}
