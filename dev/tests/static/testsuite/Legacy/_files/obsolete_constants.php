<?php
/**
 * Obsolete constants
 *
 * Format: array(<constant_name>[, <class_scope> = ''[, <replacement>]])
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
return array(
    array('BACKORDERS_BELOW'),
    array('BACKORDERS_YES'),
    array('CACHE_TAG', 'Mage_Api_Model_Config', 'Mage_Api_Model_Cache_Type::CACHE_TAG'),
    array('CACHE_TAG', 'Mage_Core_Model_AppInterface'),
    array('CACHE_TAG', 'Mage_Core_Model_Resource_Db_Collection_Abstract',
        'Mage_Core_Model_Cache_Type_Collection::CACHE_TAG'
    ),
    array('CACHE_TAG', 'Mage_Core_Model_Translate', 'Mage_Core_Model_Cache_Type_Translate::CACHE_TAG'),
    array('CACHE_TAG', 'Mage_Rss_Block_Catalog_NotifyStock'),
    array('CACHE_TAG', 'Mage_Rss_Block_Catalog_Review'),
    array('CACHE_TAG', 'Mage_Rss_Block_Order_New'),
    array('CATEGORY_APPLY_CATEGORY_AND_PRODUCT_ONLY'),
    array('CATEGORY_APPLY_CATEGORY_AND_PRODUCT_RECURSIVE'),
    array('CATEGORY_APPLY_CATEGORY_ONLY'),
    array('CATEGORY_APPLY_CATEGORY_RECURSIVE'),
    array('CHECKOUT_METHOD_GUEST'),
    array('CHECKOUT_METHOD_REGISTER'),
    array('CHECKSUM_KEY_NAME'),
    array('CONFIG_TEMPLATE_INSTALL_DATE', 'Mage_Core_Model_Config',
        'Mage_Core_Model_Config_Primary::CONFIG_TEMPLATE_INSTALL_DATE'
    ),
    array('CONFIG_XML_PATH_DEFAULT_PRODUCT_TAX_GROUP'),
    array('CONFIG_XML_PATH_DISPLAY_FULL_SUMMARY'),
    array('CONFIG_XML_PATH_DISPLAY_TAX_COLUMN'),
    array('CONFIG_XML_PATH_DISPLAY_ZERO_TAX'),
    array('CONFIG_XML_PATH_SHOW_IN_CATALOG'),
    array('DEFAULT_CURRENCY', 'Mage_Core_Model_Locale', 'Mage_Core_Model_LocaleInterface::DEFAULT_CURRENCY'),
    array('DEFAULT_ERROR_HANDLER', 'Mage_Core_Model_App', 'Mage::DEFAULT_ERROR_HANDLER'),
    array('DEFAULT_LOCALE', 'Mage_Core_Model_Locale', 'Mage_Core_Model_LocaleInterface::DEFAULT_LOCALE'),
    array('DEFAULT_THEME_NAME', 'Mage_Core_Model_Design_PackageInterface'),
    array('DEFAULT_THEME_NAME', 'Mage_Core_Model_Design_Package'),
    array('DEFAULT_TIMEZONE', 'Mage_Core_Model_Locale', 'Mage::DEFAULT_TIMEZONE'),
    array('DEFAULT_VALUE_TABLE_PREFIX'),
    array('ENTITY_PRODUCT', 'Mage_Review_Model_Review'),
    array('EXCEPTION_CODE_IS_GROUPED_PRODUCT'),
    array('FALLBACK_MAP_DIR', 'Mage_Core_Model_Design_PackageInterface'),
    array('FORMAT_TYPE_FULL', 'Mage_Core_Model_Locale', 'Mage_Core_Model_LocaleInterface::FORMAT_TYPE_FULL'),
    array('FORMAT_TYPE_LONG', 'Mage_Core_Model_Locale', 'Mage_Core_Model_LocaleInterface::FORMAT_TYPE_LONG'),
    array('FORMAT_TYPE_MEDIUM', 'Mage_Core_Model_Locale', 'Mage_Core_Model_LocaleInterface::FORMAT_TYPE_MEDIUM'),
    array('FORMAT_TYPE_SHORT', 'Mage_Core_Model_Locale', 'Mage_Core_Model_LocaleInterface::FORMAT_TYPE_SHORT'),
    array('GALLERY_IMAGE_TABLE', 'Mage_Catalog_Model_Resource_Product_Attribute_Backend_Media'),
    array('HASH_ALGO'),
    array('INIT_OPTION_DIRS', 'Mage_Core_Model_App', 'Mage::PARAM_APP_DIRS'),
    array('INIT_OPTION_REQUEST', 'Mage'),
    array('INIT_OPTION_REQUEST', 'Mage_Core_Model_App'),
    array('INIT_OPTION_RESPONSE', 'Mage'),
    array('INIT_OPTION_RESPONSE', 'Mage_Core_Model_App'),
    array('INIT_OPTION_SCOPE_CODE', 'Mage_Core_Model_App', 'Mage::PARAM_RUN_CODE'),
    array('INIT_OPTION_SCOPE_TYPE', 'Mage_Core_Model_App', 'Mage::PARAM_RUN_TYPE'),
    array('INIT_OPTION_URIS', 'Mage_Core_Model_App', 'Mage::PARAM_APP_URIS'),
    array('INSTALLER_HOST_RESPONSE', 'Mage_Install_Model_Installer'),
    array('LAYOUT_GENERAL_CACHE_TAG', 'Mage_Core_Model_Layout_Merge', 'Mage_Core_Model_Cache_Type_Layout::CACHE_TAG'),
    array('LOCALE_CACHE_KEY', 'Mage_Adminhtml_Block_Page_Footer'),
    array('LOCALE_CACHE_LIFETIME', 'Mage_Adminhtml_Block_Page_Footer'),
    array('LOCALE_CACHE_TAG', 'Mage_Adminhtml_Block_Page_Footer'),
    array('PATH_PREFIX_CUSTOMIZATION', 'Mage_Core_Model_Theme'),
    array('PATH_PREFIX_CUSTOMIZED', 'Mage_Core_Model_Theme_Files'),
    array('PUBLIC_BASE_THEME_DIR', 'Mage_Core_Model_Design_PackageInterface'),
    array('PUBLIC_CACHE_TAG', 'Mage_Core_Model_Design_PackageInterface'),
    array('PUBLIC_MODULE_DIR', 'Mage_Core_Model_Design_PackageInterface',
        'Mage_Core_Model_Design_Package::PUBLIC_MODULE_DIR'
    ),
    array('PUBLIC_THEME_DIR', 'Mage_Core_Model_Design_PackageInterface',
        'Mage_Core_Model_Design_Package::PUBLIC_THEME_DIR'
    ),
    array('PUBLIC_VIEW_DIR', 'Mage_Core_Model_Design_PackageInterface',
        'Mage_Core_Model_Design_Package::PUBLIC_VIEW_DIR'
    ),
    array('REGISTRY_FORM_PARAMS_KEY', null, 'direct value'),
    array('SCOPE_TYPE_GROUP', 'Mage_Core_Model_App', 'Mage_Core_Model_StoreManagerInterface::SCOPE_TYPE_GROUP'),
    array('SCOPE_TYPE_STORE', 'Mage_Core_Model_App', 'Mage_Core_Model_StoreManagerInterface::SCOPE_TYPE_STORE'),
    array('SCOPE_TYPE_WEBSITE', 'Mage_Core_Model_App', 'Mage_Core_Model_StoreManagerInterface::SCOPE_TYPE_WEBSITE'),
    array('SEESION_MAX_COOKIE_LIFETIME'),
    array('TYPE_BINARY', null, 'Varien_Db_Ddl_Table::TYPE_BLOB'),
    array('TYPE_CHAR', null, 'Varien_Db_Ddl_Table::TYPE_TEXT'),
    array('TYPE_CLOB', null, 'Varien_Db_Ddl_Table::TYPE_TEXT'),
    array('TYPE_DOUBLE', null, 'Varien_Db_Ddl_Table::TYPE_FLOAT'),
    array('TYPE_LONGVARBINARY', null, 'Varien_Db_Ddl_Table::TYPE_BLOB'),
    array('TYPE_LONGVARCHAR', null, 'Varien_Db_Ddl_Table::TYPE_TEXT'),
    array('TYPE_REAL', null, 'Varien_Db_Ddl_Table::TYPE_FLOAT'),
    array('TYPE_TIME', null, 'Varien_Db_Ddl_Table::TYPE_TIMESTAMP'),
    array('TYPE_TINYINT', null, 'Varien_Db_Ddl_Table::TYPE_SMALLINT'),
    array('TYPE_VARCHAR', null, 'Varien_Db_Ddl_Table::TYPE_TEXT'),
    array('URL_TYPE_SKIN'),
    array('XML_PATH_ALLOW_CODES', 'Mage_Core_Model_Locale', 'Mage_Core_Model_LocaleInterface::XML_PATH_ALLOW_CODES'),
    array('XML_PATH_ALLOW_CURRENCIES', 'Mage_Core_Model_Locale',
        'Mage_Core_Model_LocaleInterface::XML_PATH_ALLOW_CURRENCIES'
    ),
    array('XML_PATH_ALLOW_CURRENCIES_INSTALLED', 'Mage_Core_Model_Locale',
        'Mage_Core_Model_LocaleInterface::XML_PATH_ALLOW_CURRENCIES_INSTALLED'),
    array('XML_PATH_ALLOW_DUPLICATION', 'Mage_Core_Model_Design_PackageInterface',
        'Mage_Core_Model_Design_Package::XML_PATH_ALLOW_DUPLICATION'
    ),
    array('XML_PATH_ALLOW_MAP_UPDATE', 'Mage_Core_Model_Design_PackageInterface'),
    array('XML_PATH_CACHE_BETA_TYPES'),
    array('XML_PATH_CHECK_EXTENSIONS', 'Mage_Install_Model_Config'),
    array('XML_PATH_COUNTRY_DEFAULT', 'Mage_Paypal_Model_System_Config_Backend_MerchantCountry'),
    array('XML_PATH_DEFAULT_COUNTRY', 'Mage_Core_Model_Locale'),
    array('XML_PATH_DEFAULT_LOCALE', 'Mage_Core_Model_Locale',
        'Mage_Core_Model_LocaleInterface::XML_PATH_DEFAULT_LOCALE'
    ),
    array('XML_PATH_DEFAULT_TIMEZONE', 'Mage_Core_Model_Locale',
        'Mage_Core_Model_LocaleInterface::XML_PATH_DEFAULT_TIMEZONE'
    ),
    array('XML_PATH_INSTALL_DATE', 'Mage_Core_Model_App', 'Mage_Core_Model_Config_Primary::XML_PATH_INSTALL_DATE'),
    array('XML_PATH_LOCALE_INHERITANCE', 'Mage_Core_Model_Translate',
        'Mage_Core_Model_Locale_Hierarchy_Loader::XML_PATH_LOCALE_INHERITANCE'
    ),
    array('XML_PATH_SENDING_SET_RETURN_PATH', 'Mage_Newsletter_Model_Subscriber'),
    array('XML_PATH_SKIP_PROCESS_MODULES_UPDATES', 'Mage_Core_Model_App',
        'Mage_Core_Model_Db_UpdaterInterface::XML_PATH_SKIP_PROCESS_MODULES_UPDATES'
    ),
    array('XML_PATH_STATIC_FILE_SIGNATURE', 'Mage_Core_Helper_Data',
        'Mage_Core_Model_Design_Package::XML_PATH_STATIC_FILE_SIGNATURE'
    ),
    array('XML_PATH_THEME', 'Mage_Core_Model_Design_PackageInterface',
        'Mage_Core_Model_Design_Package::XML_PATH_THEME'
    ),
    array('XML_PATH_THEME_ID', 'Mage_Core_Model_Design_PackageInterface',
        'Mage_Core_Model_Design_Package::XML_PATH_THEME_ID'
    ),
);

