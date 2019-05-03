<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model\ResourceModel;

/**
 * Country Resource Model
 *
 * @api
 * @since 100.0.2
 */
class Country extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * Resource initialization
     *
     * @param \Magento\Framework\Escaper|null $escaper
     * @return void
     */
    protected function _construct(
        \Magento\Framework\Escaper $escaper = null
    ) {
        $this->_init('directory_country', 'country_id');
        $this->escaper = $escaper ?? \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Framework\Escaper::class
        );
    }

    /**
     * Load country by ISO code
     *
     * @param \Magento\Directory\Model\Country $country
     * @param string $code
     * @return \Magento\Directory\Model\ResourceModel\Country
     * @throws \Magento\Framework\Exception\LocalizedException
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
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please correct the country code: %1.', $this->escaper->escapeHtml($code))
                );
        }

        return $this->load($country, $code, $field);
    }
}
