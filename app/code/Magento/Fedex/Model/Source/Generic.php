<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Fedex\Model\Source;

class Generic implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Fedex\Helper\Config
     */
    protected $carrierConfig;

    /**
     * Carrier code
     *
     * @var string
     */
    protected $_code = '';

    /**
     * @param \Magento\Fedex\Helper\Config $carrierConfig
     */
    public function __construct(\Magento\Fedex\Helper\Config $carrierConfig)
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
        $configData = $this->carrierConfig->getCode($this->_code);
        $arr = [];
        foreach ($configData as $code => $title) {
            $arr[] = ['value' => $code, 'label' => $title];
        }
        return $arr;
    }
}
