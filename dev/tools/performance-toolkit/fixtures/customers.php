<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
/** @var \Magento\ToolkitFramework\Application $this */
$customersNumber = \Magento\ToolkitFramework\Config::getInstance()->getValue('customers', 10);
$this->resetObjectManager();

/** @var \Magento\Store\Model\StoreManager $storeManager */
$storeManager = $this->getObjectManager()->create('Magento\Store\Model\StoreManager');
/** @var $category \Magento\Catalog\Model\Category */
$category = $this->getObjectManager()->get('Magento\Catalog\Model\Category');
/** @var $defaultStoreView \Magento\Store\Model\Store */
$defaultStoreView = $storeManager->getDefaultStoreView();
$defaultStoreViewId = $defaultStoreView->getStoreId();
$defaultStoreViewCode = $defaultStoreView->getCode();

$result = [];
//Get all websites
$websites = $storeManager->getWebsites();
foreach ($websites as $website) {
    $result[] = $website->getCode();
}
$result = array_values($result);

$productWebsite = function ($index) use ($result) {
    return $result[$index % count($result)];
};

$pattern = [
    'email'                       => 'user_%s@example.com',
    '_website'                    => $productWebsite,
    '_store'                      => $defaultStoreViewCode,
    'confirmation'                => null,
    'created_at'                  => '30-08-2012 17:43',
    'created_in'                  => 'Default',
    'default_billing'             => '1',
    'default_shipping'            => '1',
    'disable_auto_group_change'   => '0',
    'dob'                         => '12-10-1991',
    'firstname'                   => 'Firstname',
    'gender'                      => 'Male',
    'group_id'                    => '1',
    'lastname'                    => 'Lastname',
    'middlename'                  => '',
    'password_hash'               => '',
    'prefix'                      => null,
    'rp_token'                    => null,
    'rp_token_created_at'         => null,
    'store_id'                    => $defaultStoreViewId,
    'suffix'                      => null,
    'taxvat'                      => null,
    'website_id'                  => '1',
    'password'                    => '123123q',
    '_address_city'               => 'Fayetteville',
    '_address_company'            => '',
    '_address_country_id'         => 'US',
    '_address_fax'                => '',
    '_address_firstname'          => 'Anthony',
    '_address_lastname'           => 'Nealy',
    '_address_middlename'         => '',
    '_address_postcode'           => '123123',
    '_address_prefix'             => '',
    '_address_region'             => 'Arkansas',
    '_address_street'             => '123 Freedom Blvd. #123',
    '_address_suffix'             => '',
    '_address_telephone'          => '022-333-4455',
    '_address_vat_id'             => '',
    '_address_default_billing_'   => '1',
    '_address_default_shipping_'  => '1',
];
$generator = new \Magento\ToolkitFramework\ImportExport\Fixture\Generator($pattern, $customersNumber);
/** @var Magento\ImportExport\Model\Import $import */
$import = $this->getObjectManager()->create(
    'Magento\ImportExport\Model\Import',
    ['data' => ['entity' => 'customer_composite', 'behavior' => 'append']]
);
// it is not obvious, but the validateSource() will actually save import queue data to DB
$import->validateSource($generator);
// this converts import queue into actual entities
$import->importSource();
