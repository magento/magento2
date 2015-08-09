<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Usps\Model\Source;

use Magento\Shipping\Model\Carrier\Source\GenericInterface;
use Magento\Usps\Model\Carrier;

/**
 * Generic source
 */
class Generic implements GenericInterface
{
    /**
     * @var \Magento\Usps\Helper\Config
     */
    protected $carrierConfig;

    /**
     * Carrier code
     *
     * @var string
     */
    protected $_code = '';

    /**
     * @param \Magento\Usps\Helper\Config $carrierConfig
     */
    public function __construct(\Magento\Usps\Helper\Config $carrierConfig)
    {
        $this->carrierConfig = $carrierConfig;
    }


    /**
     * Returns array to be used in multiselect on back-end
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $codes = $this->carrierConfig->getCode($this->code);
        if ($codes) {
            foreach ($codes as $code => $title) {
                $options[] = ['value' => $code, 'label' => __($title)];
            }
        }
        return $options;
    }
}
