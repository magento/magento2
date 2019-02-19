<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Model;

/**
 * Region
 *
 * @method string getRegionId()
 * @method string getCountryId()
 * @method \Magento\Directory\Model\Region setCountryId(string $value)
 * @method string getCode()
 * @method \Magento\Directory\Model\Region setCode(string $value)
 * @method string getDefaultName()
 * @method \Magento\Directory\Model\Region setDefaultName(string $value)
 *
 * @api
 * @since 100.0.2
 */
class Region extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Directory\Model\ResourceModel\Region::class);
    }

    /**
     * Retrieve region name
     *
     * If name is not declared, then default_name is used
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
