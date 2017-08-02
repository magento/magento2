<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Fedex\Model\Source;

/**
 * Class \Magento\Fedex\Model\Source\Generic
 *
 * @since 2.0.0
 */
class Generic implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Fedex\Model\Carrier
     * @since 2.0.0
     */
    protected $_shippingFedex;

    /**
     * Carrier code
     *
     * @var string
     * @since 2.0.0
     */
    protected $_code = '';

    /**
     * @param \Magento\Fedex\Model\Carrier $shippingFedex
     * @since 2.0.0
     */
    public function __construct(\Magento\Fedex\Model\Carrier $shippingFedex)
    {
        $this->_shippingFedex = $shippingFedex;
    }

    /**
     * Returns array to be used in multiselect on back-end
     *
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        $configData = $this->_shippingFedex->getCode($this->_code);
        $arr = [];
        foreach ($configData as $code => $title) {
            $arr[] = ['value' => $code, 'label' => $title];
        }
        return $arr;
    }
}
