<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model\Resource;

/**
 * Directory Country Resource Model
 */
class Country extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('directory_country', 'country_id');
    }

    /**
     * Load country by ISO code
     *
     * @param \Magento\Directory\Model\Country $country
     * @param string $code
     * @return \Magento\Directory\Model\Resource\Country
     * @throws \Magento\Framework\Model\Exception
     */
    public function loadByCode(\Magento\Directory\Model\Country $country, $code)
    {
        switch (strlen($code)) {
            case 2:
                $field = 'iso2_code';
                break;

            case 3:
                $field = 'iso3_code';
                break;

            default:
                throw new \Magento\Framework\Model\Exception(__('Please correct the country code: %1.', $code));
        }

        return $this->load($country, $code, $field);
    }
}
