<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

use Magento\Framework\App\ResourceConnection;

/**
 * Class OrdersFixture
 */
class OrdersFixture extends Fixture
{
    /**
     * @var int
     */
    protected $priority = 135;

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD)
     */
    public function execute()
    {
        $ordersCount = $this->fixtureModel->getValue('orders', 0);
        if ($ordersCount < 1) {
            return;
        }
        $this->fixtureModel->resetObjectManager();

        $connection = $this->getConnection();

        $quoteTableName = $this->getTableName(
            'quote',
            'Magento\Quote\Model\ResourceModel\Quote'
        );
        $quoteAddressTableName = $this->getTableName(
            'quote_address',
            'Magento\Quote\Model\ResourceModel\Quote\Address'
        );
        $quoteItemTableName = $this->getTableName(
            'quote_item',
            'Magento\Quote\Model\ResourceModel\Quote\Item'
        );
        $quoteItemOptionTableName = $this->getTableName(
            'quote_item_option',
            'Magento\Quote\Model\ResourceModel\Quote\Item\Option'
        );
        $quotePaymentTableName = $this->getTableName(
            'quote_payment',
            'Magento\Quote\Model\ResourceModel\Quote\Payment'
        );
        $quoteAddressRateTableName = $this->getTableName(
            'quote_shipping_rate',
            'Magento\Quote\Model\ResourceModel\Quote\Address\Rate'
        );
        $reportEventTableName = $this->getTableName(
            'report_event',
            'Magento\Reports\Model\ResourceModel\Event'
        );
        $salesOrderTableName = $this->getTableName(
            'sales_order',
            'Magento\Sales\Model\ResourceModel\Order'
        );
        $salesOrderAddressTableName = $this->getTableName(
            'sales_order_address',
            'Magento\Sales\Model\ResourceModel\Order'
        );
        $salesOrderGridTableName = $this->getTableName(
            'sales_order_grid',
            'Magento\Sales\Model\ResourceModel\Order\Grid'
        );
        $salesOrderItemTableName = $this->getTableName(
            'sales_order_item',
            'Magento\Sales\Model\ResourceModel\Order\Item'
        );
        $salesOrderPaymentTableName = $this->getTableName(
            'sales_order_payment',
            'Magento\Sales\Model\ResourceModel\Order\Payment'
        );
        $salesOrderStatusHistoryTableName = $this->getTableName(
            'sales_order_status_history',
            'Magento\Sales\Model\ResourceModel\Order\Status\History'
        );
        $eavEntityStoreTableName = $this->getTableName(
            'eav_entity_store',
            '\Magento\Eav\Model\ResourceModel\Entity\Store'
        );
        /** @var \Magento\Store\Model\StoreManager $storeManager */
        $storeManager = $this->fixtureModel->getObjectManager()->create('Magento\Store\Model\StoreManager');
        /** @var $category \Magento\Catalog\Model\Category */
        $category = $this->fixtureModel->getObjectManager()->get('Magento\Catalog\Model\Category');
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $this->fixtureModel->getObjectManager()->get('Magento\Catalog\Model\Product');

        $result = [];
        $stores = $storeManager->getStores();
        foreach ($stores as $store) {
            $storeId = $store->getStoreId();
            $websiteId = $store->getWebsite()->getId();
            $websiteName = $store->getWebsite()->getName();
            $groupName = $store->getGroup()->getName();
            $storeName = $store->getName();
            $storeRootCategory = $store->getRootCategoryId();
            $category->load($storeRootCategory);
            $categoryResource = $category->getResource();
            //Get all categories
            $resultsCategories = $categoryResource->getAllChildren($category);
            foreach ($resultsCategories as $resultsCategory) {
                $category->load($resultsCategory);
                $structure = explode('/', $category->getPath());
                $pathSize = count($structure);
                if ($pathSize > 1) {
                    $path = [];
                    for ($i = 1; $i < $pathSize; $i++) {
                        $path[] = $category->load($structure[$i])->getName();
                    }
                    array_shift($path);
                    $resultsCategoryName = implode('/', $path);
                } else {
                    $resultsCategoryName = $category->getName();
                }
                //Not use root categories
                if (trim($resultsCategoryName) != '') {
                    /** @var $productCategory \Magento\Catalog\Model\Category */
                    $productCategory = $this->fixtureModel->getObjectManager()->get('Magento\Catalog\Model\Category');

                    /** @var $simpleProductCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
                    $simpleProductCollection = $this->fixtureModel->getObjectManager()->create(
                        'Magento\Catalog\Model\ResourceModel\Product\Collection'
                    );

                    $simpleProductCollection->addStoreFilter($storeId);
                    $simpleProductCollection->addWebsiteFilter($websiteId);
                    $simpleProductCollection->addCategoryFilter($productCategory->load($resultsCategory));
                    $simpleProductCollection->getSelect()->where(" type_id = 'simple' ");
                    $simpleIds = $simpleProductCollection->getAllIds(2);
                    $simpleProductsResult = [];
                    foreach ($simpleIds as $key => $simpleId) {
                        $simpleProduct = $product->load($simpleId);
                        $simpleProductsResult[$key]['simpleProductId'] = $simpleId;
                        $simpleProductsResult[$key]['simpleProductSku'] = $simpleProduct->getSku();
                        $simpleProductsResult[$key]['simpleProductName'] = $simpleProduct->getName();
                    }

                    $result[] = [
                        $storeId,
                        $websiteName. '\n'. $groupName . '\n' . $storeName,
                        $simpleProductsResult
                    ];
                }
            }
        }

        $productStoreId = function ($index) use ($result) {
            return $result[$index % count($result)][0];
        };
        $productStoreName = function ($index) use ($result) {
            return $result[$index % count($result)][1];
        };

        $simpleProductId[0] = function ($index) use ($result) {
            return $result[$index % count($result)][2][0]['simpleProductId'];
        };
        $simpleProductId[1] = function ($index) use ($result) {
            return $result[$index % count($result)][2][1]['simpleProductId'];
        };
        $simpleProductSku[0] = function ($index) use ($result) {
            return $result[$index % count($result)][2][0]['simpleProductSku'];
        };
        $simpleProductSku[1] = function ($index) use ($result) {
            return $result[$index % count($result)][2][1]['simpleProductSku'];
        };
        $simpleProductName[0] = function ($index) use ($result) {
            return $result[$index % count($result)][2][0]['simpleProductName'];
        };
        $simpleProductName[1] = function ($index) use ($result) {
            return $result[$index % count($result)][2][1]['simpleProductName'];
        };

        $entityId = 1;
        while ($entityId <= $ordersCount) {
            $queries = [];

            $orderNumber = 100000000 * $productStoreId($entityId) + $entityId;
            $email = 'order_' . $entityId . '@example.com';
            $firstName = 'First Name';
            $lastName = 'Last Name';
            $company = 'Company';
            $address = 'Address';
            $city = 'City';
            $state = 'Alabama';
            $country = 'US';
            $zip = '11111';
            $phone = '911';
            $dateStart = new \DateTime();
            $time = $dateStart->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);

            $simpleProductIdLen[0] = strlen($simpleProductId[0]($entityId));
            $simpleProductIdLen[1] = strlen($simpleProductId[1]($entityId));

            //@codingStandardsIgnoreStart

            $queries[] = "INSERT INTO `{$eavEntityStoreTableName}` (`entity_store_id`, `entity_type_id`, `store_id`, `increment_prefix`, `increment_last_id`) VALUES ({$productStoreId($entityId)}, 5, {$productStoreId($entityId)}, '{$productStoreId($entityId)}', '{$orderNumber}') ON DUPLICATE KEY UPDATE `increment_last_id`='{$orderNumber}';";

            $quoteId = $entityId;
            $queries[] = "INSERT INTO `{$quoteTableName}` (`entity_id`, `store_id`, `created_at`, `updated_at`, `converted_at`, `is_active`, `is_virtual`, `is_multi_shipping`, `items_count`, `items_qty`, `orig_order_id`, `store_to_base_rate`, `store_to_quote_rate`, `base_currency_code`, `store_currency_code`, `quote_currency_code`, `grand_total`, `base_grand_total`, `checkout_method`, `customer_id`, `customer_tax_class_id`, `customer_group_id`, `customer_email`, `customer_prefix`, `customer_firstname`, `customer_middlename`, `customer_lastname`, `customer_suffix`, `customer_dob`, `customer_note`, `customer_note_notify`, `customer_is_guest`, `remote_ip`, `applied_rule_ids`, `reserved_order_id`, `password_hash`, `coupon_code`, `global_currency_code`, `base_to_global_rate`, `base_to_quote_rate`, `customer_taxvat`, `customer_gender`, `subtotal`, `base_subtotal`, `subtotal_with_discount`, `base_subtotal_with_discount`, `is_changed`, `trigger_recollect`, `ext_shipping_info`, `is_persistent`, `gift_message_id`) VALUES ({$quoteId}, {$productStoreId($entityId)}, '{$time}', '1970-01-01 03:00:00', NULL, 0, 0, 0, 2, 2.0000, 0, 0.0000, 0.0000, 'USD', 'USD', 'USD', 25.3000, 25.3000, 'guest', NULL, 3, 0, '{$email}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, '127.0.0.1', '1', NULL, NULL, NULL, 'USD', 1.0000, 1.0000, NULL, NULL, 17.0000, 17.0000, 15.3000, 15.3000, 1, 0, NULL, 0, NULL);";

            $quoteAddressId[0] = $entityId * 2 - 1;
            $quoteAddressId[1] = $entityId * 2;
            $queries[] = "INSERT INTO `{$quoteAddressTableName}` (`address_id`, `quote_id`, `created_at`, `updated_at`, `customer_id`, `save_in_address_book`, `customer_address_id`, `address_type`, `email`, `prefix`, `firstname`, `middlename`, `lastname`, `suffix`, `company`, `street`, `city`, `region`, `region_id`, `postcode`, `country_id`, `telephone`, `fax`, `same_as_billing`, `collect_shipping_rates`, `shipping_method`, `shipping_description`, `weight`, `subtotal`, `base_subtotal`, `subtotal_with_discount`, `base_subtotal_with_discount`, `tax_amount`, `base_tax_amount`, `shipping_amount`, `base_shipping_amount`, `shipping_tax_amount`, `base_shipping_tax_amount`, `discount_amount`, `base_discount_amount`, `grand_total`, `base_grand_total`, `customer_notes`, `applied_taxes`, `discount_description`, `shipping_discount_amount`, `base_shipping_discount_amount`, `subtotal_incl_tax`, `base_subtotal_total_incl_tax`, `discount_tax_compensation_amount`, `base_discount_tax_compensation_amount`, `shipping_discount_tax_compensation_amount`, `base_shipping_discount_tax_compensation_amnt`, `shipping_incl_tax`, `base_shipping_incl_tax`, `free_shipping`, `vat_id`, `vat_is_valid`, `vat_request_id`, `vat_request_date`, `vat_request_success`, `gift_message_id`) VALUES ({$quoteAddressId[0]}, {$quoteId}, '{$time}', '1970-01-01 03:00:00', NULL, 1, NULL, 'billing', '{$email}', NULL, '{$firstName}', NULL, '{$lastName}', NULL, '{$company}', '{$address}', '{$city}', '{$state}', 1, '{$zip}', '{$country}', '{$phone}', NULL, 0, 0, NULL, NULL, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, NULL, NULL, 0.0000, 0.0000, 0.0000, 0.0000, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, 0.0000, 0.0000, 0.0000, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL);";
            $queries[] = "INSERT INTO `{$quoteAddressTableName}` (`address_id`, `quote_id`, `created_at`, `updated_at`, `customer_id`, `save_in_address_book`, `customer_address_id`, `address_type`, `email`, `prefix`, `firstname`, `middlename`, `lastname`, `suffix`, `company`, `street`, `city`, `region`, `region_id`, `postcode`, `country_id`, `telephone`, `fax`, `same_as_billing`, `collect_shipping_rates`, `shipping_method`, `shipping_description`, `weight`, `subtotal`, `base_subtotal`, `subtotal_with_discount`, `base_subtotal_with_discount`, `tax_amount`, `base_tax_amount`, `shipping_amount`, `base_shipping_amount`, `shipping_tax_amount`, `base_shipping_tax_amount`, `discount_amount`, `base_discount_amount`, `grand_total`, `base_grand_total`, `customer_notes`, `applied_taxes`, `discount_description`, `shipping_discount_amount`, `base_shipping_discount_amount`, `subtotal_incl_tax`, `base_subtotal_total_incl_tax`, `discount_tax_compensation_amount`, `base_discount_tax_compensation_amount`, `shipping_discount_tax_compensation_amount`, `base_shipping_discount_tax_compensation_amnt`, `shipping_incl_tax`, `base_shipping_incl_tax`, `free_shipping`, `vat_id`, `vat_is_valid`, `vat_request_id`, `vat_request_date`, `vat_request_success`, `gift_message_id`) VALUES ({$quoteAddressId[1]}, {$quoteId}, '{$time}', '1970-01-01 03:00:00', NULL, 0, NULL, 'shipping', '{$email}', NULL, '{$firstName}', NULL, '{$lastName}', NULL, '{$company}', '{$address}', '{$city}', '{$state}', 1, '{$zip}', '{$country}', '{$phone}', NULL, 1, 0, 'flatrate_flatrate', 'Flat Rate - Fixed', 2.0000, 17.0000, 17.0000, 0.0000, 0.0000, 0.0000, 0.0000, 10.0000, 10.0000, 0.0000, 0.0000, -1.7000, -1.7000, 25.3000, 25.3000, NULL, 'a:0:{}', NULL, 0.0000, 0.0000, 17.0000, NULL, 0.0000, 0.0000, 0.0000, NULL, 10.0000, 10.0000, 0, NULL, NULL, NULL, NULL, NULL, NULL);";

            $quoteItemId[0] = $entityId * 4 - 3;
            $quoteItemId[1] = $entityId * 4 - 2;
            $quoteItemId[2] = $entityId * 4 - 1;
            $quoteItemId[3] = $entityId * 4;
            $queries[] = "INSERT INTO `{$quoteItemTableName}` (`item_id`, `quote_id`, `created_at`, `updated_at`, `product_id`, `store_id`, `parent_item_id`, `is_virtual`, `sku`, `name`, `description`, `applied_rule_ids`, `additional_data`, `is_qty_decimal`, `no_discount`, `weight`, `qty`, `price`, `base_price`, `custom_price`, `discount_percent`, `discount_amount`, `base_discount_amount`, `tax_percent`, `tax_amount`, `base_tax_amount`, `row_total`, `base_row_total`, `row_total_with_discount`, `row_weight`, `product_type`, `base_tax_before_discount`, `tax_before_discount`, `original_custom_price`, `redirect_url`, `base_cost`, `price_incl_tax`, `base_price_incl_tax`, `row_total_incl_tax`, `base_row_total_incl_tax`, `discount_tax_compensation_amount`, `base_discount_tax_compensation_amount`, `free_shipping`, `gift_message_id`, `weee_tax_applied`, `weee_tax_applied_amount`, `weee_tax_applied_row_amount`, `weee_tax_disposition`, `weee_tax_row_disposition`, `base_weee_tax_applied_amount`, `base_weee_tax_applied_row_amnt`, `base_weee_tax_disposition`, `base_weee_tax_row_disposition`) VALUES ({$quoteItemId[0]}, {$quoteId}, '1970-01-01 03:00:00', '1970-01-01 03:00:00', {$simpleProductId[0]($entityId)}, {$productStoreId($entityId)}, NULL, 0, '{$simpleProductSku[0]($entityId)}', '{$simpleProductName[0]($entityId)}', NULL, '1', NULL, 0, 0, 1.0000, 1.0000, 8.5000, 8.5000, NULL, 10.0000, 0.8500, 0.8500, 0.0000, 0.0000, 0.0000, 8.5000, 8.5000, 0.0000, 1.0000, 'simple', NULL, NULL, NULL, NULL, NULL, 8.5000, 8.5000, 8.5000, 8.5000, 0.0000, 0.0000, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);";
            $queries[] = "INSERT INTO `{$quoteItemTableName}` (`item_id`, `quote_id`, `created_at`, `updated_at`, `product_id`, `store_id`, `parent_item_id`, `is_virtual`, `sku`, `name`, `description`, `applied_rule_ids`, `additional_data`, `is_qty_decimal`, `no_discount`, `weight`, `qty`, `price`, `base_price`, `custom_price`, `discount_percent`, `discount_amount`, `base_discount_amount`, `tax_percent`, `tax_amount`, `base_tax_amount`, `row_total`, `base_row_total`, `row_total_with_discount`, `row_weight`, `product_type`, `base_tax_before_discount`, `tax_before_discount`, `original_custom_price`, `redirect_url`, `base_cost`, `price_incl_tax`, `base_price_incl_tax`, `row_total_incl_tax`, `base_row_total_incl_tax`, `discount_tax_compensation_amount`, `base_discount_tax_compensation_amount`, `free_shipping`, `gift_message_id`, `weee_tax_applied`, `weee_tax_applied_amount`, `weee_tax_applied_row_amount`, `weee_tax_disposition`, `weee_tax_row_disposition`, `base_weee_tax_applied_amount`, `base_weee_tax_applied_row_amnt`, `base_weee_tax_disposition`, `base_weee_tax_row_disposition`) VALUES ({$quoteItemId[1]}, {$quoteId}, '1970-01-01 03:00:00', '1970-01-01 03:00:00', {$simpleProductId[1]($entityId)}, {$productStoreId($entityId)}, NULL, 0, '{$simpleProductSku[1]($entityId)}', '{$simpleProductName[1]($entityId)}', NULL, '1', NULL, 0, 0, 1.0000, 1.0000, 8.5000, 8.5000, NULL, 10.0000, 0.8500, 0.8500, 0.0000, 0.0000, 0.0000, 8.5000, 8.5000, 0.0000, 1.0000, 'simple', NULL, NULL, NULL, NULL, NULL, 8.5000, 8.5000, 8.5000, 8.5000, 0.0000, 0.0000, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);";

            $quoteItemOptionId[0] = $entityId * 8 - 7;
            $quoteItemOptionId[1] = $entityId * 8 - 6;
            $quoteItemOptionId[2] = $entityId * 8 - 5;
            $quoteItemOptionId[3] = $entityId * 8 - 4;
            $quoteItemOptionId[4] = $entityId * 8 - 3;
            $quoteItemOptionId[5] = $entityId * 8 - 2;
            $quoteItemOptionId[6] = $entityId * 8 - 1;
            $quoteItemOptionId[7] = $entityId * 8;
            $queries[] = "INSERT INTO `{$quoteItemOptionTableName}` (`option_id`, `item_id`, `product_id`, `code`, `value`) VALUES ({$quoteItemOptionId[0]}, {$quoteItemId[0]}, {$simpleProductId[0]($entityId)}, 'info_buyRequest', 'a:3:{s:4:\"uenc\";s:44:\"aHR0cDovL21hZ2UyLmNvbS9jYXRlZ29yeS0xLmh0bWw,\";s:7:\"product\";s:{$simpleProductIdLen[0]}:\"{$simpleProductId[0]($entityId)}\";s:3:\"qty\";i:1;}');";
            $queries[] = "INSERT INTO `{$quoteItemOptionTableName}` (`option_id`, `item_id`, `product_id`, `code`, `value`) VALUES ({$quoteItemOptionId[1]}, {$quoteItemId[1]}, {$simpleProductId[1]($entityId)}, 'info_buyRequest', 'a:3:{s:4:\"uenc\";s:44:\"aHR0cDovL21hZ2UyLmNvbS9jYXRlZ29yeS0xLmh0bWw,\";s:7:\"product\";s:{$simpleProductIdLen[1]}:\"{$simpleProductId[1]($entityId)}\";s:3:\"qty\";i:1;}');";

            $quotePaymentId = $quoteId;
            $queries[] = "INSERT INTO `{$quotePaymentTableName}` (`payment_id`, `quote_id`, `created_at`, `updated_at`, `method`, `cc_type`, `cc_number_enc`, `cc_last_4`, `cc_cid_enc`, `cc_owner`, `cc_exp_month`, `cc_exp_year`, `cc_ss_owner`, `cc_ss_start_month`, `cc_ss_start_year`, `po_number`, `additional_data`, `cc_ss_issue`, `additional_information`) VALUES ({$quotePaymentId}, {$quoteId}, '{$time}', '1970-01-01 03:00:00', 'checkmo', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, NULL, NULL, NULL, NULL);";

            $quoteShippingRateId = $quoteAddressId[1];
            $queries[] = "INSERT INTO `{$quoteAddressRateTableName}` (`rate_id`, `address_id`, `created_at`, `updated_at`, `carrier`, `carrier_title`, `code`, `method`, `method_description`, `price`, `error_message`, `method_title`) VALUES ({$quoteShippingRateId}, {$quoteAddressId[1]}, '{$time}', '1970-01-01 03:00:00', 'flatrate', 'Flat Rate', 'flatrate_flatrate', 'flatrate', NULL, 10.0000, NULL, 'Fixed');";

            $reportEventId[0] = $quoteItemId[0];
            $reportEventId[1] = $quoteItemId[1];
            $reportEventId[2] = $quoteItemId[2];
            $reportEventId[3] = $quoteItemId[3];
            $queries[] = "INSERT INTO `{$reportEventTableName}` (`event_id`, `logged_at`, `event_type_id`, `object_id`, `subject_id`, `subtype`, `store_id`) VALUES ({$reportEventId[0]}, '{$time}', 4, {$simpleProductId[0]($entityId)}, 2, 1, {$productStoreId($entityId)});";
            $queries[] = "INSERT INTO `{$reportEventTableName}` (`event_id`, `logged_at`, `event_type_id`, `object_id`, `subject_id`, `subtype`, `store_id`) VALUES ({$reportEventId[1]}, '{$time}', 4, {$simpleProductId[1]($entityId)}, 2, 1, {$productStoreId($entityId)});";

            $salesOrderId = $quoteId;
            $queries[] = "INSERT INTO `{$salesOrderTableName}` (`entity_id`, `state`, `status`, `coupon_code`, `protect_code`, `shipping_description`, `is_virtual`, `store_id`, `customer_id`, `base_discount_amount`, `base_discount_canceled`, `base_discount_invoiced`, `base_discount_refunded`, `base_grand_total`, `base_shipping_amount`, `base_shipping_canceled`, `base_shipping_invoiced`, `base_shipping_refunded`, `base_shipping_tax_amount`, `base_shipping_tax_refunded`, `base_subtotal`, `base_subtotal_canceled`, `base_subtotal_invoiced`, `base_subtotal_refunded`, `base_tax_amount`, `base_tax_canceled`, `base_tax_invoiced`, `base_tax_refunded`, `base_to_global_rate`, `base_to_order_rate`, `base_total_canceled`, `base_total_invoiced`, `base_total_invoiced_cost`, `base_total_offline_refunded`, `base_total_online_refunded`, `base_total_paid`, `base_total_qty_ordered`, `base_total_refunded`, `discount_amount`, `discount_canceled`, `discount_invoiced`, `discount_refunded`, `grand_total`, `shipping_amount`, `shipping_canceled`, `shipping_invoiced`, `shipping_refunded`, `shipping_tax_amount`, `shipping_tax_refunded`, `store_to_base_rate`, `store_to_order_rate`, `subtotal`, `subtotal_canceled`, `subtotal_invoiced`, `subtotal_refunded`, `tax_amount`, `tax_canceled`, `tax_invoiced`, `tax_refunded`, `total_canceled`, `total_invoiced`, `total_offline_refunded`, `total_online_refunded`, `total_paid`, `total_qty_ordered`, `total_refunded`, `can_ship_partially`, `can_ship_partially_item`, `customer_is_guest`, `customer_note_notify`, `billing_address_id`, `customer_group_id`, `edit_increment`, `email_sent`, `send_email`, `forced_shipment_with_invoice`, `payment_auth_expiration`, `quote_address_id`, `quote_id`, `shipping_address_id`, `adjustment_negative`, `adjustment_positive`, `base_adjustment_negative`, `base_adjustment_positive`, `base_shipping_discount_amount`, `base_subtotal_incl_tax`, `base_total_due`, `payment_authorization_amount`, `shipping_discount_amount`, `subtotal_incl_tax`, `total_due`, `weight`, `customer_dob`, `increment_id`, `applied_rule_ids`, `base_currency_code`, `customer_email`, `customer_firstname`, `customer_lastname`, `customer_middlename`, `customer_prefix`, `customer_suffix`, `customer_taxvat`, `discount_description`, `ext_customer_id`, `ext_order_id`, `global_currency_code`, `hold_before_state`, `hold_before_status`, `order_currency_code`, `original_increment_id`, `relation_child_id`, `relation_child_real_id`, `relation_parent_id`, `relation_parent_real_id`, `remote_ip`, `shipping_method`, `store_currency_code`, `store_name`, `x_forwarded_for`, `customer_note`, `created_at`, `updated_at`, `total_item_count`, `customer_gender`, `discount_tax_compensation_amount`, `base_discount_tax_compensation_amount`, `shipping_discount_tax_compensation_amount`, `base_shipping_discount_tax_compensation_amnt`, `discount_tax_compensation_invoiced`, `base_discount_tax_compensation_invoiced`, `discount_tax_compensation_refunded`, `base_discount_tax_compensation_refunded`, `shipping_incl_tax`, `base_shipping_incl_tax`, `coupon_rule_name`, `gift_message_id`) VALUES ({$salesOrderId}, 'new', 'pending', NULL, '272ecb', 'Flat Rate - Fixed', 0, {$productStoreId($entityId)}, NULL, -1.7000, NULL, NULL, NULL, 25.3000, 10.0000, NULL, NULL, NULL, 0.0000, NULL, 17.0000, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, 1.0000, 1.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, -1.7000, NULL, NULL, NULL, 25.3000, 10.0000, NULL, NULL, NULL, 0.0000, NULL, 0.0000, 0.0000, 17.0000, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2.0000, NULL, NULL, NULL, 1, 1, 2, 0, NULL, 1, 1, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, 17.0000, 25.3000, NULL, NULL, 17.0000, 25.3000, 2.0000, NULL, {$orderNumber}, '1', 'USD', '{$email}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'USD', NULL, NULL, 'USD', NULL, NULL, NULL, NULL, NULL, '127.0.0.1', 'flatrate_flatrate', 'USD', '{$productStoreName($entityId)}', NULL, NULL, '{$time}', '{$time}', 2, NULL, 0.0000, 0.0000, 0.0000, NULL, NULL, NULL, NULL, NULL, 10.0000, 10.0000, NULL, NULL);";

            $salesOrderAddressId[0] = $quoteAddressId[0];
            $salesOrderAddressId[1] = $quoteAddressId[1];
            $queries[] = "INSERT INTO `{$salesOrderAddressTableName}` (`entity_id`, `parent_id`, `customer_address_id`, `quote_address_id`, `region_id`, `customer_id`, `fax`, `region`, `postcode`, `lastname`, `street`, `city`, `email`, `telephone`, `country_id`, `firstname`, `address_type`, `prefix`, `middlename`, `suffix`, `company`, `vat_id`, `vat_is_valid`, `vat_request_id`, `vat_request_date`, `vat_request_success`) VALUES ({$salesOrderAddressId[0]}, {$salesOrderId}, NULL, NULL, 1, NULL, NULL, '{$state}', '{$zip}', '{$lastName}', '{$address}', '{$city}', '{$email}', '{$phone}', '{$country}', '{$firstName}', 'shipping', NULL, NULL, NULL, '{$company}', NULL, NULL, NULL, NULL, NULL);";
            $queries[] = "INSERT INTO `{$salesOrderAddressTableName}` (`entity_id`, `parent_id`, `customer_address_id`, `quote_address_id`, `region_id`, `customer_id`, `fax`, `region`, `postcode`, `lastname`, `street`, `city`, `email`, `telephone`, `country_id`, `firstname`, `address_type`, `prefix`, `middlename`, `suffix`, `company`, `vat_id`, `vat_is_valid`, `vat_request_id`, `vat_request_date`, `vat_request_success`) VALUES ({$salesOrderAddressId[1]}, {$salesOrderId}, NULL, NULL, 1, NULL, NULL, '{$state}', '{$zip}', '{$lastName}', '{$address}', '{$city}', '{$email}', '{$phone}', '{$country}', '{$firstName}', 'billing', NULL, NULL, NULL, '{$company}', NULL, NULL, NULL, NULL, NULL);";

            $salesOrderGridId = $salesOrderId;
            $queries[] = "INSERT INTO `{$salesOrderGridTableName}` (`entity_id`, `status`, `store_id`, `store_name`, `customer_id`, `base_grand_total`, `base_total_paid`, `grand_total`, `total_paid`, `increment_id`, `base_currency_code`, `order_currency_code`, `shipping_name`, `billing_name`, `created_at`, `updated_at`) VALUES ({$salesOrderGridId}, 'pending', {$productStoreId($entityId)}, '{$productStoreName($entityId)}', NULL, 25.3000, NULL, 25.3000, NULL, {$orderNumber}, 'USD', 'USD', '', '', '{$time}', NULL);";

            $salesOrderItemId[0] = $quoteItemId[0];
            $salesOrderItemId[1] = $quoteItemId[1];
            $salesOrderItemId[2] = $quoteItemId[2];
            $salesOrderItemId[3] = $quoteItemId[3];
            $queries[] = "INSERT INTO `{$salesOrderItemTableName}` (`item_id`, `order_id`, `parent_item_id`, `quote_item_id`, `store_id`, `created_at`, `updated_at`, `product_id`, `product_type`, `product_options`, `weight`, `is_virtual`, `sku`, `name`, `description`, `applied_rule_ids`, `additional_data`, `is_qty_decimal`, `no_discount`, `qty_backordered`, `qty_canceled`, `qty_invoiced`, `qty_ordered`, `qty_refunded`, `qty_shipped`, `base_cost`, `price`, `base_price`, `original_price`, `base_original_price`, `tax_percent`, `tax_amount`, `base_tax_amount`, `tax_invoiced`, `base_tax_invoiced`, `discount_percent`, `discount_amount`, `base_discount_amount`, `discount_invoiced`, `base_discount_invoiced`, `amount_refunded`, `base_amount_refunded`, `row_total`, `base_row_total`, `row_invoiced`, `base_row_invoiced`, `row_weight`, `base_tax_before_discount`, `tax_before_discount`, `ext_order_item_id`, `locked_do_invoice`, `locked_do_ship`, `price_incl_tax`, `base_price_incl_tax`, `row_total_incl_tax`, `base_row_total_incl_tax`, `discount_tax_compensation_amount`, `base_discount_tax_compensation_amount`, `discount_tax_compensation_invoiced`, `base_discount_tax_compensation_invoiced`, `discount_tax_compensation_refunded`, `base_discount_tax_compensation_refunded`, `tax_canceled`, `discount_tax_compensation_canceled`, `tax_refunded`, `base_tax_refunded`, `discount_refunded`, `base_discount_refunded`, `free_shipping`, `gift_message_id`, `gift_message_available`, `weee_tax_applied`, `weee_tax_applied_amount`, `weee_tax_applied_row_amount`, `weee_tax_disposition`, `weee_tax_row_disposition`, `base_weee_tax_applied_amount`, `base_weee_tax_applied_row_amnt`, `base_weee_tax_disposition`, `base_weee_tax_row_disposition`) VALUES ({$salesOrderItemId[0]}, {$salesOrderId}, NULL, {$quoteItemId[0]}, {$productStoreId($entityId)}, '{$time}', '0000-00-00 00:00:00', {$simpleProductId[0]($entityId)}, 'simple', 'a:1:{s:15:\"info_buyRequest\";a:3:{s:4:\"uenc\";s:44:\"aHR0cDovL21hZ2UyLmNvbS9jYXRlZ29yeS0xLmh0bWw,\";s:7:\"product\";s:{$simpleProductIdLen[0]}:\"{$simpleProductId[0]($entityId)}\";s:3:\"qty\";i:1;}}', 1.0000, 0, '{$simpleProductSku[0]($entityId)}', '{$simpleProductName[0]($entityId)}', NULL, '1', NULL, 0, 0, NULL, 0.0000, 0.0000, 1.0000, 0.0000, 0.0000, NULL, 8.5000, 8.5000, 10.0000, 10.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 10.0000, 0.8500, 0.8500, 0.0000, 0.0000, 0.0000, 0.0000, 8.5000, 8.5000, 0.0000, 0.0000, 1.0000, NULL, NULL, NULL, NULL, NULL, 8.5000, 8.5000, 8.5000, 8.5000, 0.0000, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);";
            $queries[] = "INSERT INTO `{$salesOrderItemTableName}` (`item_id`, `order_id`, `parent_item_id`, `quote_item_id`, `store_id`, `created_at`, `updated_at`, `product_id`, `product_type`, `product_options`, `weight`, `is_virtual`, `sku`, `name`, `description`, `applied_rule_ids`, `additional_data`, `is_qty_decimal`, `no_discount`, `qty_backordered`, `qty_canceled`, `qty_invoiced`, `qty_ordered`, `qty_refunded`, `qty_shipped`, `base_cost`, `price`, `base_price`, `original_price`, `base_original_price`, `tax_percent`, `tax_amount`, `base_tax_amount`, `tax_invoiced`, `base_tax_invoiced`, `discount_percent`, `discount_amount`, `base_discount_amount`, `discount_invoiced`, `base_discount_invoiced`, `amount_refunded`, `base_amount_refunded`, `row_total`, `base_row_total`, `row_invoiced`, `base_row_invoiced`, `row_weight`, `base_tax_before_discount`, `tax_before_discount`, `ext_order_item_id`, `locked_do_invoice`, `locked_do_ship`, `price_incl_tax`, `base_price_incl_tax`, `row_total_incl_tax`, `base_row_total_incl_tax`, `discount_tax_compensation_amount`, `base_discount_tax_compensation_amount`, `discount_tax_compensation_invoiced`, `base_discount_tax_compensation_invoiced`, `discount_tax_compensation_refunded`, `base_discount_tax_compensation_refunded`, `tax_canceled`, `discount_tax_compensation_canceled`, `tax_refunded`, `base_tax_refunded`, `discount_refunded`, `base_discount_refunded`, `free_shipping`, `gift_message_id`, `gift_message_available`, `weee_tax_applied`, `weee_tax_applied_amount`, `weee_tax_applied_row_amount`, `weee_tax_disposition`, `weee_tax_row_disposition`, `base_weee_tax_applied_amount`, `base_weee_tax_applied_row_amnt`, `base_weee_tax_disposition`, `base_weee_tax_row_disposition`) VALUES ({$salesOrderItemId[1]}, {$salesOrderId}, NULL, {$quoteItemId[1]}, {$productStoreId($entityId)}, '{$time}', '0000-00-00 00:00:00', {$simpleProductId[1]($entityId)}, 'simple', 'a:1:{s:15:\"info_buyRequest\";a:3:{s:4:\"uenc\";s:44:\"aHR0cDovL21hZ2UyLmNvbS9jYXRlZ29yeS0xLmh0bWw,\";s:7:\"product\";s:{$simpleProductIdLen[1]}:\"{$simpleProductId[1]($entityId)}\";s:3:\"qty\";i:1;}}', 1.0000, 0, '{$simpleProductSku[1]($entityId)}', '{$simpleProductName[1]($entityId)}', NULL, '1', NULL, 0, 0, NULL, 0.0000, 0.0000, 1.0000, 0.0000, 0.0000, NULL, 8.5000, 8.5000, 10.0000, 10.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 10.0000, 0.8500, 0.8500, 0.0000, 0.0000, 0.0000, 0.0000, 8.5000, 8.5000, 0.0000, 0.0000, 1.0000, NULL, NULL, NULL, NULL, NULL, 8.5000, 8.5000, 8.5000, 8.5000, 0.0000, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);";

            $salesOrderPaymentId = $salesOrderId;
            $queries[] = "INSERT INTO `{$salesOrderPaymentTableName}` (`entity_id`, `parent_id`, `base_shipping_captured`, `shipping_captured`, `amount_refunded`, `base_amount_paid`, `amount_canceled`, `base_amount_authorized`, `base_amount_paid_online`, `base_amount_refunded_online`, `base_shipping_amount`, `shipping_amount`, `amount_paid`, `amount_authorized`, `base_amount_ordered`, `base_shipping_refunded`, `shipping_refunded`, `base_amount_refunded`, `amount_ordered`, `base_amount_canceled`, `quote_payment_id`, `additional_data`, `cc_exp_month`, `cc_ss_start_year`, `echeck_bank_name`, `method`, `cc_debug_request_body`, `cc_secure_verify`, `protection_eligibility`, `cc_approval`, `cc_last_4`, `cc_status_description`, `echeck_type`, `cc_debug_response_serialized`, `cc_ss_start_month`, `echeck_account_type`, `last_trans_id`, `cc_cid_status`, `cc_owner`, `cc_type`, `po_number`, `cc_exp_year`, `cc_status`, `echeck_routing_number`, `account_status`, `anet_trans_method`, `cc_debug_response_body`, `cc_ss_issue`, `echeck_account_name`, `cc_avs_status`, `cc_number_enc`, `cc_trans_id`, `address_status`, `additional_information`) VALUES ({$salesOrderPaymentId}, {$salesOrderId}, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 10.0000, 10.0000, NULL, NULL, 25.3000, NULL, NULL, NULL, 25.3000, NULL, NULL, NULL, NULL, '0', NULL, 'checkmo', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'a:1:{s:53:\"a:1:{s:12:\"method_title\";s:19:\"Check / Money order\";}\";N;}');";

            $salesOrderStatusHistoryId = $salesOrderId;
            $queries[] = "INSERT INTO `{$salesOrderStatusHistoryTableName}` (`entity_id`, `parent_id`, `is_customer_notified`, `is_visible_on_front`, `comment`, `status`, `created_at`, `entity_name`) VALUES ({$salesOrderStatusHistoryId}, {$salesOrderId}, 1, 0, NULL, 'pending', '{$time}', 'order');";

            // @codingStandardsIgnoreEnd
            foreach ($queries as $query) {
                $connection->query($query);
            }

            $entityId++;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getActionTitle()
    {
        return 'Generating orders';
    }

    /**
     * {@inheritdoc}
     */
    public function introduceParamLabels()
    {
        return [
            'orders'     => 'Orders'
        ];
    }

    /**
     * Get real table name for db table, validated by db adapter
     *
     * @param string $tableName
     * @param string $resourceName
     * @return string
     */
    public function getTableName($tableName, $resourceName)
    {
        $resource = $this->fixtureModel->getObjectManager()->get($resourceName);
        return $this->getConnection()->getTableName($resource->getTable($tableName));
    }

    /**
     * Retrieve connection to resource specified by $resourceName
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface|false
     */
    public function getConnection()
    {
        return $this->fixtureModel->getObjectManager()->get(
            'Magento\Framework\App\ResourceConnection'
        )->getConnection();
    }
}
