<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Store\Model\Store;
use Magento\Store\Model\Information;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\Order\Shipment;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$store = $objectManager->get(StoreManagerInterface::class)->getStore();
/** @var MutableScopeConfigInterface $mutableConfig */
$mutableConfig = $objectManager->get(MutableScopeConfigInterface::class);
$mutableConfig->setValue(Information::XML_PATH_STORE_INFO_NAME, 'Sample Store', ScopeInterface::SCOPE_STORE);
$mutableConfig->setValue(Store::XML_PATH_UNSECURE_BASE_LINK_URL, 'http://m2.com/', ScopeInterface::SCOPE_STORE);
$mutableConfig->setValue(Shipment::XML_PATH_STORE_ADDRESS1, '6161 West Centinela Avenue', ScopeInterface::SCOPE_STORE);
$mutableConfig->setValue(Shipment::XML_PATH_STORE_ADDRESS2, 'app. 111', ScopeInterface::SCOPE_STORE);
$mutableConfig->setValue(Shipment::XML_PATH_STORE_CITY, 'Culver City', ScopeInterface::SCOPE_STORE);
$mutableConfig->setValue(Shipment::XML_PATH_STORE_REGION_ID, 10, ScopeInterface::SCOPE_STORE);
$mutableConfig->setValue(Shipment::XML_PATH_STORE_ZIP, '90230', ScopeInterface::SCOPE_STORE);
$mutableConfig->setValue(Shipment::XML_PATH_STORE_COUNTRY_ID, 'US', ScopeInterface::SCOPE_STORE);

$mutableConfig->setValue(Information::XML_PATH_STORE_INFO_STREET_LINE1, '5th Avenue', ScopeInterface::SCOPE_STORE);
$mutableConfig->setValue(Information::XML_PATH_STORE_INFO_STREET_LINE2, '75', ScopeInterface::SCOPE_STORE);
$mutableConfig->setValue(Information::XML_PATH_STORE_INFO_CITY, 'New York', ScopeInterface::SCOPE_STORE);
$mutableConfig->setValue(Information::XML_PATH_STORE_INFO_REGION_CODE, 30, ScopeInterface::SCOPE_STORE);
$mutableConfig->setValue(Information::XML_PATH_STORE_INFO_POSTCODE, '19032', ScopeInterface::SCOPE_STORE);
$mutableConfig->setValue(Information::XML_PATH_STORE_INFO_COUNTRY_CODE, 'US', ScopeInterface::SCOPE_STORE);
