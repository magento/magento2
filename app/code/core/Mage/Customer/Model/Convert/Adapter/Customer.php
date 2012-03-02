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
 * @category    Mage
 * @package     Mage_Customer
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Customer_Model_Convert_Adapter_Customer
    extends Mage_Eav_Model_Convert_Adapter_Entity
{
    const MULTI_DELIMITER = ' , ';

    /**
     * Customer model
     *
     * @var Mage_Customer_Model_Customer
     */
    protected $_customerModel;
    protected $_stores;
    protected $_attributes = array();
    protected $_customerGroups;

    protected $_billingAddressModel;
    protected $_shippingAddressModel;

    protected $_requiredFields = array();

    protected $_ignoreFields = array();

    protected $_billingFields = array();

    protected $_billingMappedFields = array();

    protected $_billingStreetFields = array();

    protected $_billingRequiredFields = array();

    protected $_shippingFields = array();

    protected $_shippingMappedFields = array();

    protected $_shippingStreetFields= array();

    protected $_shippingRequiredFields = array();

    protected $_addressFields = array();

    protected $_regions;
    protected $_websites;

    protected $_customer = null;
    protected $_address = null;

    protected $_customerId = '';

    /**
     * Retrieve customer model cache
     *
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomerModel()
    {
        if (is_null($this->_customerModel)) {
            $object = Mage::getModel('Mage_Customer_Model_Customer');
            $this->_customerModel = Mage::objects()->save($object);
        }
        return Mage::objects()->load($this->_customerModel);
    }

    /**
     * Retrieve customer address model cache
     *
     * @return Mage_Customer_Model_Address
     */
    public function getBillingAddressModel()
    {
        if (is_null($this->_billingAddressModel)) {
            $object = Mage::getModel('Mage_Customer_Model_Address');
            $this->_billingAddressModel = Mage::objects()->save($object);
        }
        return Mage::objects()->load($this->_billingAddressModel);
    }

    /**
     * Retrieve customer address model cache
     *
     * @return Mage_Customer_Model_Address
     */
    public function getShippingAddressModel()
    {
        if (is_null($this->_shippingAddressModel)) {
            $object = Mage::getModel('Mage_Customer_Model_Address');
            $this->_shippingAddressModel = Mage::objects()->save($object);
        }
        return Mage::objects()->load($this->_shippingAddressModel);
    }

    /**
     * Retrieve store object by code
     *
     * @param string $store
     * @return Mage_Core_Model_Store
     */
    public function getStoreByCode($store)
    {
        if (is_null($this->_stores)) {
            $this->_stores = Mage::app()->getStores(true, true);
        }
        if (isset($this->_stores[$store])) {
            return $this->_stores[$store];
        }
        return false;
    }

    /**
     * Retrieve website model by code
     *
     * @param int $websiteId
     * @return Mage_Core_Model_Website
     */
    public function getWebsiteByCode($websiteCode)
    {
        if (is_null($this->_websites)) {
            $this->_websites = Mage::app()->getWebsites(true, true);
        }
        if (isset($this->_websites[$websiteCode])) {
            return $this->_websites[$websiteCode];
        }
        return false;
    }

    /**
     * Retrieve eav entity attribute model
     *
     * @param string $code
     * @return Mage_Eav_Model_Entity_Attribute
     */
    public function getAttribute($code)
    {
        if (!isset($this->_attributes[$code])) {
            $this->_attributes[$code] = $this->getCustomerModel()->getResource()->getAttribute($code);
        }
        return $this->_attributes[$code];
    }

    /**
     * Retrieve region id by country code and region name (if exists)
     *
     * @param string $country
     * @param string $region
     * @return int
     */
    public function getRegionId($country, $regionName)
    {
        if (is_null($this->_regions)) {
            $this->_regions = array();

            $collection = Mage::getModel('Mage_Directory_Model_Region')
                ->getCollection();
            foreach ($collection as $region) {
                if (!isset($this->_regions[$region->getCountryId()])) {
                    $this->_regions[$region->getCountryId()] = array();
                }

                $this->_regions[$region->getCountryId()][$region->getDefaultName()] = $region->getId();
            }
        }

        if (isset($this->_regions[$country][$regionName])) {
            return $this->_regions[$country][$regionName];
        }

        return 0;
    }

    /**
     * Retrieve customer group collection array
     *
     * @return array
     */
    public function getCustomerGroups()
    {
        if (is_null($this->_customerGroups)) {
            $this->_customerGroups = array();
            $collection = Mage::getModel('Mage_Customer_Model_Group')
                ->getCollection()
                ->addFieldToFilter('customer_group_id', array('gt'=> 0));
            foreach ($collection as $group) {
                $this->_customerGroups[$group->getCustomerGroupCode()] = $group->getId();
            }
        }
        return $this->_customerGroups;
    }

    /**
     * Alias at getCustomerGroups()
     *
     * @return array
     */
    public function getCustomerGoups()
    {
        return $this->getCustomerGroups();
    }

    public function __construct()
    {
        $this->setVar('entity_type', 'customer/customer')
            ->setVar('entity_resource', 'Mage_Customer_Model_Resource_Customer');

        if (!Mage::registry('Object_Cache_Customer')) {
            $this->setCustomer(Mage::getModel('Mage_Customer_Model_Customer'));
        }

        foreach (Mage::getConfig()->getFieldset('customer_dataflow', 'admin') as $code=>$node) {
            if ($node->is('ignore')) {
                $this->_ignoreFields[] = $code;
            }
            if ($node->is('billing')) {
                $this->_billingFields[] = 'billing_'.$code;
            }
            if ($node->is('shipping')) {
                $this->_shippingFields[] = 'shipping_'.$code;
            }

            if ($node->is('billing') && $node->is('shipping')) {
                $this->_addressFields[] = $code;
            }

            if ($node->is('mapped') || $node->is('billing_mapped')) {
                $this->_billingMappedFields['billing_'.$code] = $code;
            }
            if ($node->is('mapped') || $node->is('shipping_mapped')) {
                $this->_shippingMappedFields['shipping_'.$code] = $code;
            }
            if ($node->is('street')) {
                $this->_billingStreetFields[] = 'billing_'.$code;
                $this->_shippingStreetFields[] = 'shipping_'.$code;
            }
            if ($node->is('required')) {
                $this->_requiredFields[] = $code;
            }
            if ($node->is('billing_required')) {
                $this->_billingRequiredFields[] = 'billing_'.$code;
            }
            if ($node->is('shipping_required')) {
                $this->_shippingRequiredFields[] = 'shipping_'.$code;
            }
        }
    }

    public function load()
    {
        $addressType = $this->getVar('filter/adressType'); //error in key filter addressType
        if ($addressType=='both') {
           $addressType = array('default_billing','default_shipping');
        }
        $attrFilterArray = array();
        $attrFilterArray ['firstname']                  = 'like';
        $attrFilterArray ['lastname']                   = 'like';
        $attrFilterArray ['email']                      = 'like';
        $attrFilterArray ['group']                      = 'eq';
        $attrFilterArray ['customer_address/telephone'] = array(
            'type'  => 'like',
            'bind'  => $addressType
        );
        $attrFilterArray ['customer_address/postcode']  = array(
            'type'  => 'like',
            'bind'  => $addressType
        );
        $attrFilterArray ['customer_address/country']   = array(
            'type'  => 'eq',
            'bind'  => $addressType
        );
        $attrFilterArray ['customer_address/region']    = array(
            'type'  => 'like',
            'bind'  => $addressType
        );
        $attrFilterArray ['created_at']                 = 'datetimeFromTo';

        /*
         * Fixing date filter from and to
         */
        if ($var = $this->getVar('filter/created_at/from')) {
            $this->setVar('filter/created_at/from', $var . ' 00:00:00');
        }

        if ($var = $this->getVar('filter/created_at/to')) {
            $this->setVar('filter/created_at/to', $var . ' 23:59:59');
        }

        $attrToDb = array(
            'group'                     => 'group_id',
            'customer_address/country'  => 'customer_address/country_id',
        );

        // Added store filter
        if ($storeId = $this->getStoreId()) {
            $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
            if ($websiteId) {
                $this->_filter[] = array(
                    'attribute' => 'website_id',
                    'eq'        => $websiteId
                );
            }
        }

        parent::setFilter($attrFilterArray, $attrToDb);
        return parent::load();
    }

    /**
     * Not use :(
     */
    public function parse()
    {
        $batchModel = Mage::getSingleton('Mage_Dataflow_Model_Batch');
        /* @var $batchModel Mage_Dataflow_Model_Batch */

        $batchImportModel = $batchModel->getBatchImportModel();
        $importIds = $batchImportModel->getIdCollection();

        foreach ($importIds as $importId) {
            $batchImportModel->load($importId);
            $importData = $batchImportModel->getBatchData();

            $this->saveRow($importData);
        }
    }

    public function setCustomer(Mage_Customer_Model_Customer $customer)
    {
        $id = Mage::objects()->save($customer);
        Mage::register('Object_Cache_Customer', $id);
    }

    public function getCustomer()
    {
        return Mage::objects()->load(Mage::registry('Object_Cache_Customer'));
    }

    public function save()
    {
        $stores = array();
        foreach (Mage::getConfig()->getNode('stores')->children() as $storeNode) {
            $stores[(int)$storeNode->system->store->id] = $storeNode->getName();
        }

        $collections = $this->getData();
        if ($collections instanceof Mage_Customer_Model_Resource_Customer_Collection) {
            $collections = array($collections->getEntity()->getStoreId()=>$collections);
        } elseif (!is_array($collections)) {
            $this->addException(Mage::helper('Mage_Customer_Helper_Data')->__('No customer collections found'), Mage_Dataflow_Model_Convert_Exception::FATAL);
        }

        foreach ($collections as $storeId=>$collection) {
            $this->addException(Mage::helper('Mage_Customer_Helper_Data')->__('Records for %s store found.', $stores[$storeId]));

            if (!$collection instanceof Mage_Customer_Model_Resource_Customer_Collection) {
                $this->addException(Mage::helper('Mage_Customer_Helper_Data')->__('Customer collection expected.'), Mage_Dataflow_Model_Convert_Exception::FATAL);
            }
            try {
                $i = 0;
                foreach ($collection->getIterator() as $model) {
                    $new = false;
                    // if customer is new, create default values first
                    if (!$model->getId()) {
                        $new = true;
                        $model->save();
                    }
                    if (!$new || 0!==$storeId) {
                        $model->save();
                    }
                    $i++;
                }
                $this->addException(Mage::helper('Mage_Customer_Helper_Data')->__("Saved %d record(s)", $i));
            } catch (Exception $e) {
                if (!$e instanceof Mage_Dataflow_Model_Convert_Exception) {
                    $this->addException(Mage::helper('Mage_Customer_Helper_Data')->__('An error occurred while saving the collection, aborting. Error: %s', $e->getMessage()),
                        Mage_Dataflow_Model_Convert_Exception::FATAL);
                }
            }
        }
        return $this;
    }

    /*
     * saveRow function for saving each customer data
     *
     * params args array
     * return array
     */
    public function saveRow($importData)
    {
        $customer = $this->getCustomerModel();
        $customer->setId(null);

        if (empty($importData['website'])) {
            $message = Mage::helper('Mage_Customer_Helper_Data')->__('Skipping import row, required field "%s" is not defined.', 'website');
            Mage::throwException($message);
        }

        $website = $this->getWebsiteByCode($importData['website']);

        if ($website === false) {
            $message = Mage::helper('Mage_Customer_Helper_Data')->__('Skipping import row, website "%s" field does not exist.', $importData['website']);
            Mage::throwException($message);
        }
        if (empty($importData['email'])) {
            $message = Mage::helper('Mage_Customer_Helper_Data')->__('Skipping import row, required field "%s" is not defined.', 'email');
            Mage::throwException($message);
        }

        $customer->setWebsiteId($website->getId())
            ->loadByEmail($importData['email']);
        if (!$customer->getId()) {
            $customerGroups = $this->getCustomerGroups();
            /**
             * Check customer group
             */
            if (empty($importData['group']) || !isset($customerGroups[$importData['group']])) {
                $value = isset($importData['group']) ? $importData['group'] : '';
                $message = Mage::helper('Mage_Catalog_Helper_Data')->__('Skipping import row, the value "%s" is not valid for the "%s" field.', $value, 'group');
                Mage::throwException($message);
            }
            $customer->setGroupId($customerGroups[$importData['group']]);

            foreach ($this->_requiredFields as $field) {
                if (!isset($importData[$field])) {
                    $message = Mage::helper('Mage_Catalog_Helper_Data')->__('Skip import row, required field "%s" for the new customer is not defined.', $field);
                    Mage::throwException($message);
                }
            }

            $customer->setWebsiteId($website->getId());

            if (empty($importData['created_in']) || !$this->getStoreByCode($importData['created_in'])) {
                $customer->setStoreId(0);
            }
            else {
                $customer->setStoreId($this->getStoreByCode($importData['created_in'])->getId());
            }

            if (empty($importData['password_hash'])) {
                $customer->setPasswordHash($customer->hashPassword($customer->generatePassword(8)));
            }
        }
        elseif (!empty($importData['group'])) {
            $customerGroups = $this->getCustomerGroups();
            /**
             * Check customer group
             */
            if (isset($customerGroups[$importData['group']])) {
                $customer->setGroupId($customerGroups[$importData['group']]);
            }
        }

        foreach ($this->_ignoreFields as $field) {
            if (isset($importData[$field])) {
                unset($importData[$field]);
            }
        }

        foreach ($importData as $field => $value) {
            if (in_array($field, $this->_billingFields)) {
                continue;
            }
            if (in_array($field, $this->_shippingFields)) {
                continue;
            }

            $attribute = $this->getAttribute($field);
            if (!$attribute) {
                continue;
            }

            $isArray = false;
            $setValue = $value;

            if ($attribute->getFrontendInput() == 'multiselect') {
                $value = explode(self::MULTI_DELIMITER, $value);
                $isArray = true;
                $setValue = array();
            }

            if ($attribute->usesSource()) {
                $options = $attribute->getSource()->getAllOptions(false);

                if ($isArray) {
                    foreach ($options as $item) {
                        if (in_array($item['label'], $value)) {
                            $setValue[] = $item['value'];
                        }
                    }
                }
                else {
                    $setValue = null;
                    foreach ($options as $item) {
                        if ($item['label'] == $value) {
                            $setValue = $item['value'];
                        }
                    }
                }
            }

            $customer->setData($field, $setValue);
        }

        if (isset($importData['is_subscribed'])) {
            $customer->setData('is_subscribed', $importData['is_subscribed']);
        }

        $importBillingAddress = $importShippingAddress = true;
        $savedBillingAddress = $savedShippingAddress = false;

        /**
         * Check Billing address required fields
         */
        foreach ($this->_billingRequiredFields as $field) {
            if (empty($importData[$field])) {
                $importBillingAddress = false;
            }
        }

        /**
         * Check Sipping address required fields
         */
        foreach ($this->_shippingRequiredFields as $field) {
            if (empty($importData[$field])) {
                $importShippingAddress = false;
            }
        }

        $onlyAddress = false;

        /**
         * Check addresses
         */
        if ($importBillingAddress && $importShippingAddress) {
            $onlyAddress = true;
            foreach ($this->_addressFields as $field) {
                if (!isset($importData['billing_'.$field]) && !isset($importData['shipping_'.$field])) {
                    continue;
                }
                if (!isset($importData['billing_'.$field]) || !isset($importData['shipping_'.$field])) {
                    $onlyAddress = false;
                    break;
                }
                if ($importData['billing_'.$field] != $importData['shipping_'.$field]) {
                    $onlyAddress = false;
                    break;
                }
            }

            if ($onlyAddress) {
                $importShippingAddress = false;
            }
        }

        /**
         * Import billing address
         */
        if ($importBillingAddress) {
            $billingAddress = $this->getBillingAddressModel();
            if ($customer->getDefaultBilling()) {
                $billingAddress->load($customer->getDefaultBilling());
            }
            else {
                $billingAddress->setData(array());
            }

            foreach ($this->_billingFields as $field) {
                $cleanField = Mage::helper('Mage_Core_Helper_String')->substr($field, 8);

                if (isset($importData[$field])) {
                    $billingAddress->setDataUsingMethod($cleanField, $importData[$field]);
                }
                elseif (isset($this->_billingMappedFields[$field])
                    && isset($importData[$this->_billingMappedFields[$field]])) {
                    $billingAddress->setDataUsingMethod($cleanField, $importData[$this->_billingMappedFields[$field]]);
                }
            }

            $street = array();
            foreach ($this->_billingStreetFields as $field) {
                if (!empty($importData[$field])) {
                    $street[] = $importData[$field];
                }
            }
            if ($street) {
                $billingAddress->setDataUsingMethod('street', $street);
            }

            $billingAddress->setCountryId($importData['billing_country']);
            $regionName = isset($importData['billing_region']) ? $importData['billing_region'] : '';
            if ($regionName) {
                $regionId = $this->getRegionId($importData['billing_country'], $regionName);
                $billingAddress->setRegionId($regionId);
            }

            if ($customer->getId()) {
                $billingAddress->setCustomerId($customer->getId());

                $billingAddress->save();
                $customer->setDefaultBilling($billingAddress->getId());

                if ($onlyAddress) {
                    $customer->setDefaultShipping($billingAddress->getId());
                }

                $savedBillingAddress = true;
            }
        }

        /**
         * Import shipping address
         */
        if ($importShippingAddress) {
            $shippingAddress = $this->getShippingAddressModel();
            if ($customer->getDefaultShipping() && $customer->getDefaultBilling() != $customer->getDefaultShipping()) {
                $shippingAddress->load($customer->getDefaultShipping());
            }
            else {
                $shippingAddress->setData(array());
            }

            foreach ($this->_shippingFields as $field) {
                $cleanField = Mage::helper('Mage_Core_Helper_String')->substr($field, 9);

                if (isset($importData[$field])) {
                    $shippingAddress->setDataUsingMethod($cleanField, $importData[$field]);
                }
                elseif (isset($this->_shippingMappedFields[$field])
                    && isset($importData[$this->_shippingMappedFields[$field]])) {
                    $shippingAddress->setDataUsingMethod($cleanField, $importData[$this->_shippingMappedFields[$field]]);
                }
            }

            $street = array();
            foreach ($this->_shippingStreetFields as $field) {
                if (!empty($importData[$field])) {
                    $street[] = $importData[$field];
                }
            }
            if ($street) {
                $shippingAddress->setDataUsingMethod('street', $street);
            }

            $shippingAddress->setCountryId($importData['shipping_country']);
            $regionName = isset($importData['shipping_region']) ? $importData['shipping_region'] : '';
            if ($regionName) {
                $regionId = $this->getRegionId($importData['shipping_country'], $regionName);
                $shippingAddress->setRegionId($regionId);
            }

            if ($customer->getId()) {
                $shippingAddress->setCustomerId($customer->getId());
                $shippingAddress->save();
                $customer->setDefaultShipping($shippingAddress->getId());

                $savedShippingAddress = true;
            }
        }

        $customer->setImportMode(true);
        $customer->save();
        $saveCustomer = false;

        if ($importBillingAddress && !$savedBillingAddress) {
            $saveCustomer = true;
            $billingAddress->setCustomerId($customer->getId());
            $billingAddress->save();
            $customer->setDefaultBilling($billingAddress->getId());
            if ($onlyAddress) {
                $customer->setDefaultShipping($billingAddress->getId());
            }
        }
        if ($importShippingAddress && !$savedShippingAddress) {
            $saveCustomer = true;
            $shippingAddress->setCustomerId($customer->getId());
            $shippingAddress->save();
            $customer->setDefaultShipping($shippingAddress->getId());
        }
        if ($saveCustomer) {
            $customer->save();
        }

        return $this;
    }

    public function getCustomerId()
    {
        return $this->_customerId;
    }

    /* ########### THE CODE BELOW AT THIS METHOD IS NOT USED ############# */

    public function saveRow__OLD()
    {

        $mem = memory_get_usage(); $origMem = $mem; $memory = $mem;
        $customer = $this->getCustomer();
        @set_time_limit(240);
        $row = $args;
        $newMem = memory_get_usage(); $memory .= ', '.($newMem-$mem); $mem = $newMem;
        $customer->importFromTextArray($row);

        if (!$customer->getData()) {
            return;
        }

        $newMem = memory_get_usage(); $memory .= ', '.($newMem-$mem); $mem = $newMem;
        try {
            $customer->save();
            $this->_customerId = $customer->getId();
            $customer->unsetData();
            $customer->cleanAllAddresses();
            $customer->unsetSubscription();
            $newMem = memory_get_usage(); $memory .= ', '.($newMem-$mem); $mem = $newMem;

        } catch (Exception $e) {
        }
        unset($row);
        return array('memory'=>$memory);
    }
}
