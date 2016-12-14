<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Request;

use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Information;
use Magento\Store\Model\ScopeInterface;

/**
 * Prepare data related to the seller of the product.
 * This information is optional unless you are operating a marketplace,
 * listing goods on behalf of multiple sellers who each hold a seller account registered with your site.
 */
class SellerBuilder
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * @var array
     */
    private $regionCodes = [];

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param RegionFactory $regionFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        RegionFactory $regionFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->regionFactory = $regionFactory;
    }

    /**
     * Returns seller data params
     *
     * @param Order $order
     * @return array
     */
    public function build(Order $order)
    {
        $store = $order->getStore();

        return [
            'seller' => [
                'name' => $this->getConfigValue(Information::XML_PATH_STORE_INFO_NAME, $store),
                'domain' => $this->getPublicDomain($store),
                'shipFromAddress' => [
                    'streetAddress' => $this->getConfigValue(Shipment::XML_PATH_STORE_ADDRESS1, $store),
                    'unit' => $this->getConfigValue(Shipment::XML_PATH_STORE_ADDRESS2, $store),
                    'city' => $this->getConfigValue(Shipment::XML_PATH_STORE_CITY, $store),
                    'provinceCode' => $this->getRegionCodeById(
                        $this->getConfigValue(Shipment::XML_PATH_STORE_REGION_ID, $store)
                    ),
                    'postalCode' => $this->getConfigValue(Shipment::XML_PATH_STORE_ZIP, $store),
                    'countryCode' => $this->getConfigValue(Shipment::XML_PATH_STORE_COUNTRY_ID, $store),
                ],
                'corporateAddress' => [
                    'streetAddress' => $this->getConfigValue(Information::XML_PATH_STORE_INFO_STREET_LINE1, $store),
                    'unit' => $this->getConfigValue(Information::XML_PATH_STORE_INFO_STREET_LINE2, $store),
                    'city' => $this->getConfigValue(Information::XML_PATH_STORE_INFO_CITY, $store),
                    'provinceCode' => $this->getRegionCodeById(
                        $this->getConfigValue(Information::XML_PATH_STORE_INFO_REGION_CODE, $store)
                    ),
                    'postalCode' => $this->getConfigValue(Information::XML_PATH_STORE_INFO_POSTCODE, $store),
                    'countryCode' => $this->getConfigValue(Information::XML_PATH_STORE_INFO_COUNTRY_CODE, $store),
                ]
            ]
        ];
    }

    /**
     * Get region code by id
     *
     * @param int $regionId
     * @return string
     */
    private function getRegionCodeById($regionId)
    {
        if (!isset($this->regionCodes[$regionId])) {
            $this->regionCodes[$regionId] = $this->regionFactory->create()->load($regionId)->getCode();
        }

        return $this->regionCodes[$regionId];
    }

    /**
     * Gets value from config
     *
     * @param string $value
     * @param StoreInterface $store
     * @return mixed
     */
    private function getConfigValue($value, StoreInterface $store)
    {
        return $this->scopeConfig->getValue(
            $value,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param StoreInterface $store
     *
     * @return string|null
     */
    private function getPublicDomain(StoreInterface $store)
    {
        $baseUrl = $store->getBaseUrl();
        $domain = parse_url($baseUrl, PHP_URL_HOST);
        if (\function_exists('checkdnsrr') && false === \checkdnsrr($domain)) {
            return null;
        }

        return $domain;
    }

}
