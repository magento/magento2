<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Dhl\Model\Source\Method;

/**
 * Class \Magento\Dhl\Model\Source\Method\Generic
 *
 * @since 2.0.0
 */
class Generic
{
    /**
     * @var \Magento\Dhl\Model\Carrier
     * @since 2.0.0
     */
    protected $_shippingDhl;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_code = '';

    /**
     * @param \Magento\Dhl\Model\Carrier $shippingDhl
     * @since 2.0.0
     */
    public function __construct(\Magento\Dhl\Model\Carrier $shippingDhl)
    {
        $this->_shippingDhl = $shippingDhl;
    }

    /**
     * Returns array to be used in multiselect on back-end
     *
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        $configData = $this->_shippingDhl->getCode($this->_code);
        $arr = [];
        foreach ($configData as $code => $title) {
            $arr[] = ['value' => $code, 'label' => $title];
        }
        return $arr;
    }
}
