<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Region
 *
 * @method \Magento\Directory\Model\Resource\Region _getResource()
 * @method \Magento\Directory\Model\Resource\Region getResource()
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
        $this->_init('Magento\Directory\Model\Resource\Region');
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
        if (is_null($name)) {
            $name = $this->getData('default_name');
        }
        return $name;
    }

    /**
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
