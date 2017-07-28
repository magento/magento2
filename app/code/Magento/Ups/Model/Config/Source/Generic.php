<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ups\Model\Config\Source;

use Magento\Shipping\Model\Carrier\Source\GenericInterface;

/**
 * Generic source model
 * @since 2.0.0
 */
class Generic implements GenericInterface
{
    /**
     * @var \Magento\Ups\Helper\Config
     * @since 2.0.0
     */
    protected $carrierConfig;

    /**
     * Carrier code
     *
     * @var string
     * @since 2.0.0
     */
    protected $_code = '';

    /**
     * @param \Magento\Ups\Helper\Config $carrierConfig
     * @since 2.0.0
     */
    public function __construct(\Magento\Ups\Helper\Config $carrierConfig)
    {
        $this->carrierConfig = $carrierConfig;
    }

    /**
     * Returns array to be used in multiselect on back-end
     *
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        $configData = $this->carrierConfig->getCode($this->_code);
        $arr = [];
        foreach ($configData as $code => $title) {
            $arr[] = ['value' => $code, 'label' => __($title)];
        }
        return $arr;
    }
}
