<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Region
 *
 * @method \Magento\Directory\Model\ResourceModel\Region _getResource()
 * @method \Magento\Directory\Model\ResourceModel\Region getResource()
 * @method string getRegionId()
 * @method string getCountryId()
 * @method \Magento\Directory\Model\Region setCountryId(string $value)
 * @method string getCode()
 * @method \Magento\Directory\Model\Region setCode(string $value)
 * @method string getDefaultName()
 * @method \Magento\Directory\Model\Region setDefaultName(string $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Directory\Model;

class Region extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Directory\Model\ResourceModel\Region');
    }

    /**
     * Retrieve region name
     *
     * If name is no declared, then default_name is used
     *
     * @return string
     */
    public function getName()
    {
        $name = $this->getData('name');
        if ($name === null) {
            $name = $this->getData('default_name');
        }
        return $name;
    }

    /**
     * Load region by code
     *
     * @param string $code
     * @param string $countryId
     * @return $this
     */
    public function loadByCode($code, $countryId)
    {
        if ($code) {
            $this->_getResource()->loadByCode($this, $code, $countryId);
        }
        return $this;
    }

    /**
     * Load region by name
     *
     * @param string $name
     * @param string $countryId
     * @return $this
     */
    public function loadByName($name, $countryId)
    {
        $this->_getResource()->loadByName($this, $name, $countryId);
        return $this;
    }
}
