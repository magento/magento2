<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Model\Source;

/**
 * Google Content Target country Source
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Country implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Config
     *
     * @var \Magento\GoogleShopping\Model\Config
     */
    protected $_config;

    /**
     * @param \Magento\GoogleShopping\Model\Config $config
     */
    public function __construct(\Magento\GoogleShopping\Model\Config $config)
    {
        $this->_config = $config;
    }

    /**
     * Retrieve option array with allowed countries
     *
     * @return array
     */
    public function toOptionArray()
    {
        $_allowed = $this->_config->getAllowedCountries();
        $result = [];
        foreach ($_allowed as $iso => $info) {
            $result[] = ['value' => $iso, 'label' => $info['name']];
        }
        return $result;
    }
}
