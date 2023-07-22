<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ups\Model\Config\Source;

use Magento\Shipping\Model\Carrier\Source\GenericInterface;
use Magento\Ups\Helper\Config;

/**
 * Generic source model
 */
class Generic implements GenericInterface
{
    /**
     * Carrier code
     *
     * @var string
     */
    protected $_code = '';

    /**
     * @param Config $carrierConfig
     */
    public function __construct(
        protected readonly Config $carrierConfig
    ) {
    }

    /**
     * Returns array to be used in multiselect on back-end
     *
     * @return array
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
