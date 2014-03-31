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
 * @category    Magento
 * @package     Magento_User
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/** @var $installer \Magento\Core\Model\Resource\Setup */
$installer = $this;
$installer->startSetup();

$map = array(
    'admin/system/config/feed' => 'Find_Feed::config_feed',
    'admin/catalog/feed' => 'Find_Feed::feed',
    'admin/catalog/feed/import_items' => 'Find_Feed::import_items',
    'admin/catalog/feed/import_products' => 'Find_Feed::import_products',
    'admin/system/adminnotification' => 'Magento_AdminNotification::adminnotification',
    'admin/system/adminnotification/remove' => 'Magento_AdminNotification::adminnotification_remove',
    'admin/system/adminnotification/mark_as_read' => 'Magento_AdminNotification::mark_as_read',
    'admin/system/adminnotification/show_list' => 'Magento_AdminNotification::show_list',
    'admin/system/adminnotification/show_toolbar' => 'Magento_AdminNotification::show_toolbar',
    'admin' => 'Magento_Adminhtml::admin',
    'admin/system/config/advanced' => 'Magento_Adminhtml::advanced',
    'all' => 'Magento_Adminhtml::all',
    'admin/system/cache' => 'Magento_Adminhtml::cache',
    'admin/system/config' => 'Magento_Adminhtml::config',
    'admin/system/config/admin' => 'Magento_Adminhtml::config_admin',
    'admin/system/config/design' => 'Magento_Adminhtml::config_design',
    'admin/system/config/general' => 'Magento_Adminhtml::config_general',
    'admin/system/config/system' => 'Magento_Adminhtml::config_system',
    'admin/system/convert' => 'Magento_Adminhtml::convert',
    'admin/system/config/currency' => 'Magento_Adminhtml::currency',
    'admin/system/extensions/custom' => 'Magento_Adminhtml::custom',
    'admin/dashboard' => 'Magento_Adminhtml::dashboard',
    'admin/system/design' => 'Magento_Adminhtml::design',
    'admin/system/config/dev' => 'Magento_Adminhtml::dev',
    'admin/system/email_template' => 'Magento_Email::template',
    'admin/system/extensions' => 'Magento_Adminhtml::extensions',
    'admin/global_search' => 'Magento_Adminhtml::global_search',
    'admin/system/convert/gui' => 'Magento_Adminhtml::gui',
    'admin/system/extensions/local' => 'Magento_Adminhtml::local',
    'admin/system/myaccount' => 'Magento_Adminhtml::myaccount',
    'admin/system/convert/profiles' => 'Magento_Adminhtml::profiles',
    'admin/system/design/schedule' => 'Magento_Adminhtml::schedule',
    'admin/system/config/sendfriend' => 'Magento_Adminhtml::sendfriend',
    'admin/system/store' => 'Magento_Adminhtml::store',
    'admin/system' => 'Magento_Adminhtml::system',
    'admin/system/tools' => 'Magento_Adminhtml::tools',
    'admin/system/config/trans_email' => 'Magento_Adminhtml::trans_email',
    'admin/system/variable' => 'Magento_Adminhtml::variable',
    'admin/system/config/web' => 'Magento_Adminhtml::web',
    'admin/system/tools/backup' => 'Magento_Backup::backup',
    'admin/system/tools/backup/rollback' => 'Magento_Backup::rollback',
    'admin/catalog/attributes/attributes' => 'Magento_Catalog::attributes_attributes',
    'admin/catalog' => 'Magento_Catalog::catalog',
    'admin/catalog/attributes' => 'Magento_Catalog::catalog_attributes',
    'admin/catalog/categories' => 'Magento_Catalog::categories',
    'admin/system/config/catalog' => 'Magento_Catalog::config_catalog',
    'admin/catalog/products' => 'Magento_Catalog::products',
    'admin/catalog/attributes/sets' => 'Magento_Catalog::sets',
    'admin/catalog/update_attributes' => 'Magento_Catalog::update_attributes',
    'admin/catalog/urlrewrite' => 'Magento_Catalog::urlrewrite',
    'admin/system/config/cataloginventory' => 'Magento_CatalogInventory::cataloginventory',
    'admin/promo' => 'Magento_CatalogRule::promo',
    'admin/promo/catalog' => 'Magento_CatalogRule::promo_catalog',
    'admin/catalog/search' => 'Magento_CatalogSearch::search',
    'admin/system/config/checkout' => 'Magento_Checkout::checkout',
    'admin/sales/checkoutagreement' => 'Magento_Checkout::checkoutagreement',
    'admin/cms/block' => 'Magento_Cms::block',
    'admin/cms' => 'Magento_Cms::cms',
    'admin/system/config/cms' => 'Magento_Cms::config_cms',
    'admin/cms/media_gallery' => 'Magento_Cms::media_gallery',
    'admin/cms/page' => 'Magento_Cms::page',
    'admin/cms/page/delete' => 'Magento_Cms::page_delete',
    'admin/cms/page/save' => 'Magento_Cms::save',
    'admin/system/config/contacts' => 'Magento_Contacts::contacts',
    'admin/system/currency/rates' => 'Magento_CurrencySymbol::currency_rates',
    'admin/system/currency/symbols' => 'Magento_CurrencySymbol::symbols',
    'admin/system/currency' => 'Magento_CurrencySymbol::system_currency',
    'admin/system/config/customer' => 'Magento_Customer::config_customer',
    'admin/customer' => 'Magento_Customer::customer',
    'admin/customer/group' => 'Magento_Customer::group',
    'admin/customer/manage' => 'Magento_Customer::manage',
    'admin/customer/online' => 'Magento_Customer::online',
    'admin/system/design/editor' => 'Magento_DesignEditor::editor',
    'admin/system/config/downloadable' => 'Magento_Downloadable::downloadable',
    'admin/system/config/google' => 'Magento_GoogleAnalytic::google',
    'admin/catalog/googleshopping' => 'Magento_GoogleShopping::googleshopping',
    'admin/catalog/googleshopping/items' => 'Magento_GoogleShopping::items',
    'admin/catalog/googleshopping/types' => 'Magento_GoogleShopping::types',
    'admin/system/convert/export' => 'Magento_ImportExport::export',
    'admin/system/convert/import' => 'Magento_ImportExport::import',
    'admin/system/index' => 'Magento_Index::index',
    'admin/newsletter' => 'Magento_Newsletter::admin_newsletter',
    'admin/system/config/newsletter' => 'Magento_Newsletter::newsletter',
    'admin/newsletter/problem' => 'Magento_Newsletter::problem',
    'admin/newsletter/queue' => 'Magento_Newsletter::queue',
    'admin/newsletter/subscriber' => 'Magento_Newsletter::subscriber',
    'admin/newsletter/template' => 'Magento_Newsletter::template',
    'admin/system/config/oauth' => 'Magento_Oauth::oauth',
    'admin/system/config/payment' => 'Magento_Payment::payment',
    'admin/system/config/payment_services' => 'Magento_Payment::payment_services',
    'admin/report/salesroot/paypal_settlement_reports/fetch' => 'Magento_Paypal::fetch',
    'admin/system/config/paypal' => 'Magento_Paypal::paypal',
    'admin/report/salesroot/paypal_settlement_reports' => 'Magento_Paypal::paypal_settlement_reports',
    'admin/report/salesroot/paypal_settlement_reports/view' => 'Magento_Paypal::paypal_settlement_reports_view',
    'admin/system/config/persistent' => 'Magento_Persistent::persistent',
    'admin/cms/poll' => 'Magento_Poll::poll',
    'admin/catalog/reviews_ratings/ratings' => 'Magento_Rating::ratings',
    'admin/report/shopcart/abandoned' => 'Magento_Reports::abandoned',
    'admin/report/customers/accounts' => 'Magento_Reports::accounts',
    'admin/report/products/bestsellers' => 'Magento_Reports::bestsellers',
    'admin/report/salesroot/coupons' => 'Magento_Reports::coupons',
    'admin/report/customers' => 'Magento_Reports::customers',
    'admin/report/customers/orders' => 'Magento_Reports::customers_orders',
    'admin/report/products/downloads' => 'Magento_Reports::downloads',
    'admin/report/salesroot/invoiced' => 'Magento_Reports::invoiced',
    'admin/report/products/lowstock' => 'Magento_Reports::lowstock',
    'admin/report/tags/popular' => 'Magento_Reports::popular',
    'admin/report/shopcart/product' => 'Magento_Reports::product',
    'admin/report/salesroot/refunded' => 'Magento_Reports::refunded',
    'admin/report' => 'Magento_Reports::report',
    'admin/report/products' => 'Magento_Reports::report_products',
    'admin/report/search' => 'Magento_Reports::report_search',
    'admin/system/config/reports' => 'Magento_Reports::reports',
    'admin/report/review' => 'Magento_Reports::review',
    'admin/report/review/customer' => 'Magento_Reports::review_customer',
    'admin/report/review/product' => 'Magento_Reports::review_product',
    'admin/report/salesroot' => 'Magento_Reports::salesroot',
    'admin/report/salesroot/sales' => 'Magento_Reports::salesroot_sales',
    'admin/report/salesroot/shipping' => 'Magento_Reports::shipping',
    'admin/report/shopcart' => 'Magento_Reports::shopcart',
    'admin/report/products/sold' => 'Magento_Reports::sold',
    'admin/report/statistics' => 'Magento_Reports::statistics',
    'admin/report/tags' => 'Magento_Reports::tags',
    'admin/report/tags/customer' => 'Magento_Reports::tags_customer',
    'admin/report/tags/product' => 'Magento_Reports::tags_product',
    'admin/report/salesroot/tax' => 'Magento_Reports::tax',
    'admin/report/customers/totals' => 'Magento_Reports::totals',
    'admin/report/products/viewed' => 'Magento_Reports::viewed',
    'admin/catalog/reviews_ratings/reviews/pending' => 'Magento_Review::pending',
    'admin/catalog/reviews_ratings/reviews' => 'Magento_Review::reviews',
    'admin/catalog/reviews_ratings/reviews/all' => 'Magento_Review::reviews_all',
    'admin/catalog/reviews_ratings' => 'Magento_Review::reviews_ratings',
    'admin/system/config/rss' => 'Magento_Rss::rss',
    'admin/sales/order/actions' => 'Magento_Sales::actions',
    'admin/sales/order/actions/edit' => 'Magento_Sales::actions_edit',
    'admin/paypal/billing_agreement/actions/manage' => 'Magento_Paypal::actions_manage',
    'admin/sales/order/actions/view' => 'Magento_Sales::actions_view',
    'admin/paypal/billing_agreement' => 'Magento_Paypal::billing_agreement',
    'admin/paypal/billing_agreement/actions' => 'Magento_Paypal::billing_agreement_actions',
    'admin/paypal/billing_agreement/actions/view' => 'Magento_Paypal::billing_agreement_actions_view',
    'admin/sales/order/actions/cancel' => 'Magento_Sales::cancel',
    'admin/sales/order/actions/capture' => 'Magento_Sales::capture',
    'admin/sales/order/actions/comment' => 'Magento_Sales::comment',
    'admin/system/config/sales' => 'Magento_Sales::config_sales',
    'admin/sales/order/actions/create' => 'Magento_Sales::create',
    'admin/sales/order/actions/creditmemo' => 'Magento_Sales::creditmemo',
    'admin/sales/order/actions/email' => 'Magento_Sales::email',
    'admin/sales/order/actions/emails' => 'Magento_Sales::emails',
    'admin/sales/order/actions/hold' => 'Magento_Sales::hold',
    'admin/sales/order/actions/invoice' => 'Magento_Sales::invoice',
    'admin/system/order_statuses' => 'Magento_Sales::order_statuses',
    'admin/sales/recurringPayment' => 'Magento_Sales::recurring_payment',
    'admin/sales/order/actions/reorder' => 'Magento_Sales::reorder',
    'admin/sales/order/actions/review_payment' => 'Magento_Sales::review_payment',
    'admin/sales' => 'Magento_Sales::sales',
    'admin/sales/creditmemo' => 'Magento_Sales::sales_creditmemo',
    'admin/system/config/sales_email' => 'Magento_Sales::sales_email',
    'admin/sales/invoice' => 'Magento_Sales::sales_invoice',
    'admin/sales/order' => 'Magento_Sales::sales_order',
    'admin/system/config/sales_pdf' => 'Magento_Sales::sales_pdf',
    'admin/sales/order/actions/ship' => 'Magento_Sales::ship',
    'admin/sales/shipment' => 'Magento_Sales::shipment',
    'admin/sales/transactions' => 'Magento_Sales::transactions',
    'admin/sales/transactions/fetch' => 'Magento_Sales::transactions_fetch',
    'admin/sales/order/actions/unhold' => 'Magento_Sales::unhold',
    'admin/sales/billing_agreement/actions/use' => 'Magento_Paypal::use',
    'admin/system/config/promo' => 'Magento_SalesRule::config_promo',
    'admin/promo/quote' => 'Magento_SalesRule::quote',
    'admin/system/config/carriers' => 'Magento_Shipping::carriers',
    'admin/system/config/shipping' => 'Magento_Shipping::config_shipping',
    'admin/system/config/sitemap' => 'Magento_Sitemap::config_sitemap',
    'admin/catalog/sitemap' => 'Magento_Sitemap::sitemap',
    'admin/sales/tax/classes_customer' => 'Magento_Tax::classes_customer',
    'admin/sales/tax/classes_product' => 'Magento_Tax::classes_product',
    'admin/system/config/tax' => 'Magento_Tax::config_tax',
    'admin/sales/tax/import_export' => 'Magento_Tax::import_export',
    'admin/sales/tax/rules' => 'Magento_Tax::rules',
    'admin/sales/tax' => 'Magento_Tax::sales_tax',
    'admin/sales/tax/rates' => 'Magento_Tax::tax_rates',
    'admin/system/acl' => 'Magento_User::acl',
    'admin/system/acl/roles' => 'Magento_User::acl_roles',
    'admin/system/acl/users' => 'Magento_User::acl_users',
    'admin/cms/widget_instance' => 'Magento_Widget::widget_instance',
    'admin/system/config/wishlist' => 'Magento_Wishlist::config_wishlist',
    'admin/xmlconnect/history' => 'Magento_XmlConnect::history',
    'admin/xmlconnect/mobile' => 'Magento_XmlConnect::mobile',
    'admin/xmlconnect/templates' => 'Magento_XmlConnect::templates',
    'admin/xmlconnect' => 'Magento_XmlConnect::xmlconnect',
    'admin/xmlconnect/queue' => 'Magento_XmlConnect::xmlconnect_queue',
    'admin/system/config/facebook' => 'Social_Facebook::facebook'
);

$tableName = $installer->getTable('admin_rule');
/** @var \Magento\DB\Adapter\AdapterInterface $connection */
$connection = $installer->getConnection();

$select = $connection->select();
$select->from($tableName, array())->columns(array('resource_id' => 'resource_id'))->group('resource_id');

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
