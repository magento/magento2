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
 * @package     Mage_User
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$map = array(
    'admin/system/config/feed' => 'Find_Feed::config_feed',
    'admin/catalog/feed' => 'Find_Feed::feed',
    'admin/catalog/feed/import_items' => 'Find_Feed::import_items',
    'admin/catalog/feed/import_products' => 'Find_Feed::import_products',
    'admin/system/adminnotification' => 'Mage_AdminNotification::adminnotification',
    'admin/system/adminnotification/remove' => 'Mage_AdminNotification::adminnotification_remove',
    'admin/system/adminnotification/mark_as_read' => 'Mage_AdminNotification::mark_as_read',
    'admin/system/adminnotification/show_list' => 'Mage_AdminNotification::show_list',
    'admin/system/adminnotification/show_toolbar' => 'Mage_AdminNotification::show_toolbar',
    'admin' => 'Mage_Adminhtml::admin',
    'admin/system/config/advanced' => 'Mage_Adminhtml::advanced',
    'all' => 'Mage_Adminhtml::all',
    'admin/system/cache' => 'Mage_Adminhtml::cache',
    'admin/system/config' => 'Mage_Adminhtml::config',
    'admin/system/config/admin' => 'Mage_Adminhtml::config_admin',
    'admin/system/config/design' => 'Mage_Adminhtml::config_design',
    'admin/system/config/general' => 'Mage_Adminhtml::config_general',
    'admin/system/config/system' => 'Mage_Adminhtml::config_system',
    'admin/system/convert' => 'Mage_Adminhtml::convert',
    'admin/system/config/currency' => 'Mage_Adminhtml::currency',
    'admin/system/extensions/custom' => 'Mage_Adminhtml::custom',
    'admin/dashboard' => 'Mage_Adminhtml::dashboard',
    'admin/system/design' => 'Mage_Adminhtml::design',
    'admin/system/config/dev' => 'Mage_Adminhtml::dev',
    'admin/system/email_template' => 'Mage_Adminhtml::email_template',
    'admin/system/extensions' => 'Mage_Adminhtml::extensions',
    'admin/global_search' => 'Mage_Adminhtml::global_search',
    'admin/system/convert/gui' => 'Mage_Adminhtml::gui',
    'admin/system/extensions/local' => 'Mage_Adminhtml::local',
    'admin/system/myaccount' => 'Mage_Adminhtml::myaccount',
    'admin/system/convert/profiles' => 'Mage_Adminhtml::profiles',
    'admin/system/design/schedule' => 'Mage_Adminhtml::schedule',
    'admin/system/config/sendfriend' => 'Mage_Adminhtml::sendfriend',
    'admin/system/store' => 'Mage_Adminhtml::store',
    'admin/system' => 'Mage_Adminhtml::system',
    'admin/system/tools' => 'Mage_Adminhtml::tools',
    'admin/system/config/trans_email' => 'Mage_Adminhtml::trans_email',
    'admin/system/variable' => 'Mage_Adminhtml::variable',
    'admin/system/config/web' => 'Mage_Adminhtml::web',
    'admin/system/api/rest_roles/delete' => 'Mage_Api2::delete',
    'admin/system/api/rest_attributes' => 'Mage_Api2::rest_attributes',
    'admin/system/api/rest_attributes/edit' => 'Mage_Api2::rest_attributes_edit',
    'admin/system/api/rest_roles' => 'Mage_Api2::rest_roles',
    'admin/system/api/rest_roles/add' => 'Mage_Api2::rest_roles_add',
    'admin/system/api/rest_roles/edit' => 'Mage_Api2::rest_roles_edit',
    'admin/system/api' => 'Mage_Api::api',
    'admin/system/config/api' => 'Mage_Api::config_api',
    'admin/system/api/roles' => 'Mage_Api::roles',
    'admin/system/api/users' => 'Mage_Api::users',
    'admin/system/tools/backup' => 'Mage_Backup::backup',
    'admin/system/tools/backup/rollback' => 'Mage_Backup::rollback',
    'admin/catalog/attributes/attributes' => 'Mage_Catalog::attributes_attributes',
    'admin/catalog' => 'Mage_Catalog::catalog',
    'admin/catalog/attributes' => 'Mage_Catalog::catalog_attributes',
    'admin/catalog/categories' => 'Mage_Catalog::categories',
    'admin/system/config/catalog' => 'Mage_Catalog::config_catalog',
    'admin/catalog/products' => 'Mage_Catalog::products',
    'admin/catalog/attributes/sets' => 'Mage_Catalog::sets',
    'admin/catalog/update_attributes' => 'Mage_Catalog::update_attributes',
    'admin/catalog/urlrewrite' => 'Mage_Catalog::urlrewrite',
    'admin/system/config/cataloginventory' => 'Mage_CatalogInventory::cataloginventory',
    'admin/promo' => 'Mage_CatalogRule::promo',
    'admin/promo/catalog' => 'Mage_CatalogRule::promo_catalog',
    'admin/catalog/search' => 'Mage_CatalogSearch::search',
    'admin/system/config/checkout' => 'Mage_Checkout::checkout',
    'admin/sales/checkoutagreement' => 'Mage_Checkout::checkoutagreement',
    'admin/cms/block' => 'Mage_Cms::block',
    'admin/cms' => 'Mage_Cms::cms',
    'admin/system/config/cms' => 'Mage_Cms::config_cms',
    'admin/cms/media_gallery' => 'Mage_Cms::media_gallery',
    'admin/cms/page' => 'Mage_Cms::page',
    'admin/cms/page/delete' => 'Mage_Cms::page_delete',
    'admin/cms/page/save' => 'Mage_Cms::save',
    'admin/system/config/contacts' => 'Mage_Contacts::contacts',
    'admin/system/currency/rates' => 'Mage_CurrencySymbol::currency_rates',
    'admin/system/currency/symbols' => 'Mage_CurrencySymbol::symbols',
    'admin/system/currency' => 'Mage_CurrencySymbol::system_currency',
    'admin/system/config/customer' => 'Mage_Customer::config_customer',
    'admin/customer' => 'Mage_Customer::customer',
    'admin/customer/group' => 'Mage_Customer::group',
    'admin/customer/manage' => 'Mage_Customer::manage',
    'admin/customer/online' => 'Mage_Customer::online',
    'admin/system/design/editor' => 'Mage_DesignEditor::editor',
    'admin/system/config/downloadable' => 'Mage_Downloadable::downloadable',
    'admin/system/config/google' => 'Mage_GoogleCheckout::google',
    'admin/catalog/googleshopping' => 'Mage_GoogleShopping::googleshopping',
    'admin/catalog/googleshopping/items' => 'Mage_GoogleShopping::items',
    'admin/catalog/googleshopping/types' => 'Mage_GoogleShopping::types',
    'admin/system/convert/export' => 'Mage_ImportExport::export',
    'admin/system/convert/import' => 'Mage_ImportExport::import',
    'admin/system/index' => 'Mage_Index::index',
    'admin/newsletter' => 'Mage_Newsletter::admin_newsletter',
    'admin/system/config/newsletter' => 'Mage_Newsletter::newsletter',
    'admin/newsletter/problem' => 'Mage_Newsletter::problem',
    'admin/newsletter/queue' => 'Mage_Newsletter::queue',
    'admin/newsletter/subscriber' => 'Mage_Newsletter::subscriber',
    'admin/newsletter/template' => 'Mage_Newsletter::template',
    'admin/system/api/authorizedTokens' => 'Mage_Oauth::authorizedTokens',
    'admin/system/api/consumer' => 'Mage_Oauth::consumer',
    'admin/system/api/consumer/delete' => 'Mage_Oauth::consumer_delete',
    'admin/system/api/consumer/edit' => 'Mage_Oauth::consumer_edit',
    'admin/system/config/oauth' => 'Mage_Oauth::oauth',
    'admin/system/api/oauth_admin_token' => 'Mage_Oauth::oauth_admin_token',
    'admin/page_cache' => 'Mage_PageCache::page_cache',
    'admin/system/config/payment' => 'Mage_Payment::payment',
    'admin/system/config/payment_services' => 'Mage_Payment::payment_services',
    'admin/report/salesroot/paypal_settlement_reports/fetch' => 'Mage_Paypal::fetch',
    'admin/system/config/paypal' => 'Mage_Paypal::paypal',
    'admin/report/salesroot/paypal_settlement_reports' => 'Mage_Paypal::paypal_settlement_reports',
    'admin/report/salesroot/paypal_settlement_reports/view' => 'Mage_Paypal::paypal_settlement_reports_view',
    'admin/system/config/persistent' => 'Mage_Persistent::persistent',
    'admin/cms/poll' => 'Mage_Poll::poll',
    'admin/catalog/reviews_ratings/ratings' => 'Mage_Rating::ratings',
    'admin/report/shopcart/abandoned' => 'Mage_Reports::abandoned',
    'admin/report/customers/accounts' => 'Mage_Reports::accounts',
    'admin/report/products/bestsellers' => 'Mage_Reports::bestsellers',
    'admin/report/salesroot/coupons' => 'Mage_Reports::coupons',
    'admin/report/customers' => 'Mage_Reports::customers',
    'admin/report/customers/orders' => 'Mage_Reports::customers_orders',
    'admin/report/products/downloads' => 'Mage_Reports::downloads',
    'admin/report/salesroot/invoiced' => 'Mage_Reports::invoiced',
    'admin/report/products/lowstock' => 'Mage_Reports::lowstock',
    'admin/report/tags/popular' => 'Mage_Reports::popular',
    'admin/report/shopcart/product' => 'Mage_Reports::product',
    'admin/report/salesroot/refunded' => 'Mage_Reports::refunded',
    'admin/report' => 'Mage_Reports::report',
    'admin/report/products' => 'Mage_Reports::report_products',
    'admin/report/search' => 'Mage_Reports::report_search',
    'admin/system/config/reports' => 'Mage_Reports::reports',
    'admin/report/review' => 'Mage_Reports::review',
    'admin/report/review/customer' => 'Mage_Reports::review_customer',
    'admin/report/review/product' => 'Mage_Reports::review_product',
    'admin/report/salesroot' => 'Mage_Reports::salesroot',
    'admin/report/salesroot/sales' => 'Mage_Reports::salesroot_sales',
    'admin/report/salesroot/shipping' => 'Mage_Reports::shipping',
    'admin/report/shopcart' => 'Mage_Reports::shopcart',
    'admin/report/products/sold' => 'Mage_Reports::sold',
    'admin/report/statistics' => 'Mage_Reports::statistics',
    'admin/report/tags' => 'Mage_Reports::tags',
    'admin/report/tags/customer' => 'Mage_Reports::tags_customer',
    'admin/report/tags/product' => 'Mage_Reports::tags_product',
    'admin/report/salesroot/tax' => 'Mage_Reports::tax',
    'admin/report/customers/totals' => 'Mage_Reports::totals',
    'admin/report/products/viewed' => 'Mage_Reports::viewed',
    'admin/catalog/reviews_ratings/reviews/pending' => 'Mage_Review::pending',
    'admin/catalog/reviews_ratings/reviews' => 'Mage_Review::reviews',
    'admin/catalog/reviews_ratings/reviews/all' => 'Mage_Review::reviews_all',
    'admin/catalog/reviews_ratings' => 'Mage_Review::reviews_ratings',
    'admin/system/config/rss' => 'Mage_Rss::rss',
    'admin/sales/order/actions' => 'Mage_Sales::actions',
    'admin/sales/order/actions/edit' => 'Mage_Sales::actions_edit',
    'admin/sales/billing_agreement/actions/manage' => 'Mage_Sales::actions_manage',
    'admin/sales/order/actions/view' => 'Mage_Sales::actions_view',
    'admin/sales/billing_agreement' => 'Mage_Sales::billing_agreement',
    'admin/sales/billing_agreement/actions' => 'Mage_Sales::billing_agreement_actions',
    'admin/sales/billing_agreement/actions/view' => 'Mage_Sales::billing_agreement_actions_view',
    'admin/sales/order/actions/cancel' => 'Mage_Sales::cancel',
    'admin/sales/order/actions/capture' => 'Mage_Sales::capture',
    'admin/sales/order/actions/comment' => 'Mage_Sales::comment',
    'admin/system/config/sales' => 'Mage_Sales::config_sales',
    'admin/sales/order/actions/create' => 'Mage_Sales::create',
    'admin/sales/order/actions/creditmemo' => 'Mage_Sales::creditmemo',
    'admin/sales/order/actions/email' => 'Mage_Sales::email',
    'admin/sales/order/actions/emails' => 'Mage_Sales::emails',
    'admin/sales/order/actions/hold' => 'Mage_Sales::hold',
    'admin/sales/order/actions/invoice' => 'Mage_Sales::invoice',
    'admin/system/order_statuses' => 'Mage_Sales::order_statuses',
    'admin/sales/recurring_profile' => 'Mage_Sales::recurring_profile',
    'admin/sales/order/actions/reorder' => 'Mage_Sales::reorder',
    'admin/sales/order/actions/review_payment' => 'Mage_Sales::review_payment',
    'admin/sales' => 'Mage_Sales::sales',
    'admin/sales/creditmemo' => 'Mage_Sales::sales_creditmemo',
    'admin/system/config/sales_email' => 'Mage_Sales::sales_email',
    'admin/sales/invoice' => 'Mage_Sales::sales_invoice',
    'admin/sales/order' => 'Mage_Sales::sales_order',
    'admin/system/config/sales_pdf' => 'Mage_Sales::sales_pdf',
    'admin/sales/order/actions/ship' => 'Mage_Sales::ship',
    'admin/sales/shipment' => 'Mage_Sales::shipment',
    'admin/sales/transactions' => 'Mage_Sales::transactions',
    'admin/sales/transactions/fetch' => 'Mage_Sales::transactions_fetch',
    'admin/sales/order/actions/unhold' => 'Mage_Sales::unhold',
    'admin/sales/billing_agreement/actions/use' => 'Mage_Sales::use',
    'admin/system/config/promo' => 'Mage_SalesRule::config_promo',
    'admin/promo/quote' => 'Mage_SalesRule::quote',
    'admin/system/config/carriers' => 'Mage_Shipping::carriers',
    'admin/system/config/shipping' => 'Mage_Shipping::config_shipping',
    'admin/system/config/sitemap' => 'Mage_Sitemap::config_sitemap',
    'admin/catalog/sitemap' => 'Mage_Sitemap::sitemap',
    'admin/catalog/tag' => 'Mage_Tag::tag',
    'admin/catalog/tag/all' => 'Mage_Tag::tag_all',
    'admin/catalog/tag/pending' => 'Mage_Tag::tag_pending',
    'admin/sales/tax/classes_customer' => 'Mage_Tax::classes_customer',
    'admin/sales/tax/classes_product' => 'Mage_Tax::classes_product',
    'admin/system/config/tax' => 'Mage_Tax::config_tax',
    'admin/sales/tax/import_export' => 'Mage_Tax::import_export',
    'admin/sales/tax/rules' => 'Mage_Tax::rules',
    'admin/sales/tax' => 'Mage_Tax::sales_tax',
    'admin/sales/tax/rates' => 'Mage_Tax::tax_rates',
    'admin/system/acl' => 'Mage_User::acl',
    'admin/system/acl/roles' => 'Mage_User::acl_roles',
    'admin/system/acl/users' => 'Mage_User::acl_users',
    'admin/cms/widget_instance' => 'Mage_Widget::widget_instance',
    'admin/system/config/wishlist' => 'Mage_Wishlist::config_wishlist',
    'admin/xmlconnect/history' => 'Mage_XmlConnect::history',
    'admin/xmlconnect/mobile' => 'Mage_XmlConnect::mobile',
    'admin/xmlconnect/templates' => 'Mage_XmlConnect::templates',
    'admin/xmlconnect' => 'Mage_XmlConnect::xmlconnect',
    'admin/xmlconnect/queue' => 'Mage_XmlConnect::xmlconnect_queue',
    'admin/system/config/moneybookers' => 'Phoenix_Moneybookers::moneybookers',
    'admin/system/config/facebook' => 'Social_Facebook::facebook',
);

$tableName = $installer->getTable('admin_rule');
/** @var Varien_Db_Adapter_Interface $connection */
$connection = $installer->getConnection();

$select = $connection->select();
$select->from($tableName, array())
    ->columns(array('resource_id' => 'resource_id'))
    ->group('resource_id');

foreach ($connection->fetchCol($select) as $oldKey) {
    /**
     * If used ACL key is converted previously or we haven't map for specified ACL resource item
     * than go to the next item
     */
    if (in_array($oldKey, $map) || false == isset($map[$oldKey])) {
        continue;
    }

    /** Update rule ACL key from xpath format to identifier format */
    $connection->update($tableName, array('resource_id' => $map[$oldKey]), array('resource_id = ?' => $oldKey));
}
$installer->endSetup();

