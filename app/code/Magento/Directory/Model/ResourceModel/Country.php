<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Escaper;
use Magento\Framework\App\ObjectManager;

/**
 * Country Resource Model
 *
 * @api
 * @since 100.0.2
 */
class Country extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @param Context $context
     * @param null|string $connectionName
     * @param Escaper|null $escaper
     */
    public function __construct(
        Context $context,
        ?string $connectionName = null,
        Escaper $escaper = null
    ) {
        $this->escaper = $escaper ?? ObjectManager::getInstance()->get(
            Escaper::class
        );
        parent::__construct($context, $connectionName);
    }

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
     * @return \Magento\Directory\Model\ResourceModel\Country
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByCode(\Magento\Directory\Model\Country $country, $code)
    {
        $code = $code !== null ? $code : '';
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
