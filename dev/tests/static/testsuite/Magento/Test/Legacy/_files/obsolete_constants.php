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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
return array(
    array('ADMIN_STORE_ID', 'Magento\Framework\AppInterface'),
    array('BACKORDERS_BELOW'),
    array('DS'),
    array('BACKORDERS_YES'),
    array('CACHE_TAG', 'Magento\Api\Model\Config', 'Magento_Api_Model_Cache_Type::CACHE_TAG'),
    array('CACHE_TAG', 'Magento\Framework\AppInterface'),
    array(
        'CACHE_TAG',
        'Magento\Framework\Model\Resource\Db\Collection\AbstractCollection',
        'Magento_Core_Model_Cache_Type_Collection::CACHE_TAG'
    ),
    array('CACHE_TAG', 'Magento\Framework\Translate', 'Magento_Core_Model_Cache_Type_Translate::CACHE_TAG'),
    array('CACHE_TAG', 'Magento\Rss\Block\Catalog\NotifyStock'),
    array('CACHE_TAG', 'Magento\Rss\Block\Catalog\Review'),
    array('CACHE_TAG', 'Magento\Rss\Block\Order\NewOrder'),
    array('CATEGORY_APPLY_CATEGORY_AND_PRODUCT_ONLY'),
    array('CATEGORY_APPLY_CATEGORY_AND_PRODUCT_RECURSIVE'),
    array('CATEGORY_APPLY_CATEGORY_ONLY'),
    array('CATEGORY_APPLY_CATEGORY_RECURSIVE'),
    array('CHECKOUT_METHOD_GUEST'),
    array('CHECKOUT_METHOD_REGISTER'),
    array('CHECKSUM_KEY_NAME'),
    array('CONFIG_TEMPLATE_INSTALL_DATE'),
    array('CONFIG_XML_PATH_DEFAULT_PRODUCT_TAX_GROUP'),
    array('CONFIG_XML_PATH_DISPLAY_FULL_SUMMARY'),
    array('CONFIG_XML_PATH_DISPLAY_TAX_COLUMN'),
    array('CONFIG_XML_PATH_DISPLAY_ZERO_TAX'),
    array('CONFIG_XML_PATH_SHOW_IN_CATALOG'),
    array(
        'DEFAULT_SETUP_RESOURCE',
        'Mage_Core_Model_Resource',
        'Magento_Core_Model_Config_Resource::DEFAULT_SETUP_CONNECTION'
    ),
    array(
        'DEFAULT_READ_RESOURCE',
        'Mage_Core_Model_Resource',
        'Magento_Core_Model_Config_Resource::DEFAULT_READ_CONNECTION'
    ),
    array(
        'DEFAULT_WRITE_RESOURCE',
        'Mage_Core_Model_Resource',
        'Magento_Core_Model_Config_Resource::DEFAULT_WRITE_CONNECTION'
    ),
    array('DEFAULT_CURRENCY', 'Magento\Framework\Locale', 'Magento_Core_Model_LocaleInterface::DEFAULT_CURRENCY'),
    array('DEFAULT_READ_CONNECTION', 'Magento\Framework\App\Resource\Config'),
    array('DEFAULT_WRITE_CONNECTION', 'Magento\Framework\App\Resource\Config'),
    array('DEFAULT_ERROR_HANDLER', 'Mage'),
    array('DEFAULT_LOCALE', 'Magento\Framework\Locale', 'Magento_Core_Model_LocaleInterface::DEFAULT_LOCALE'),
    array('DEFAULT_THEME_NAME', 'Magento\Core\Model\Design\PackageInterface'),
    array('DEFAULT_THEME_NAME', 'Magento\Core\Model\Design\Package'),
    array('DEFAULT_TIMEZONE', 'Magento\Framework\Locale', 'Magento_Core_Model_LocaleInterface::DEFAULT_TIMEZONE'),
    array('DEFAULT_STORE_ID', 'Magento\Catalog\Model\AbstractModel', 'Magento\Store\Model\Store::DEFAULT_STORE_ID'),
    array('DEFAULT_VALUE_TABLE_PREFIX'),
    array('ENTITY_PRODUCT', 'Magento\Review\Model\Review'),
    array('EXCEPTION_CODE_IS_GROUPED_PRODUCT'),
    array('FALLBACK_MAP_DIR', 'Magento\Core\Model\Design\PackageInterface'),
    array('FORMAT_TYPE_FULL', 'Magento\Framework\Locale', 'Magento_Core_Model_LocaleInterface::FORMAT_TYPE_FULL'),
    array('FORMAT_TYPE_LONG', 'Magento\Framework\Locale', 'Magento_Core_Model_LocaleInterface::FORMAT_TYPE_LONG'),
    array('FORMAT_TYPE_MEDIUM', 'Magento\Framework\Locale', 'Magento_Core_Model_LocaleInterface::FORMAT_TYPE_MEDIUM'),
    array('FORMAT_TYPE_SHORT', 'Magento\Framework\Locale', 'Magento_Core_Model_LocaleInterface::FORMAT_TYPE_SHORT'),
    array('GALLERY_IMAGE_TABLE', 'Magento\Catalog\Model\Resource\Product\Attribute\Backend\Media'),
    array('HASH_ALGO'),
    array('INIT_OPTION_DIRS', 'Magento\Core\Model\App', 'Magento_Core_Model_App::PARAM_APP_DIRS'),
    array('INIT_OPTION_REQUEST', 'Magento\Core\Model\App'),
    array('INIT_OPTION_RESPONSE', 'Magento\Core\Model\App'),
    array('INIT_OPTION_SCOPE_CODE', 'Magento\Core\Model\App', 'Magento_Core_Model_App::PARAM_RUN_CODE'),
    array('INIT_OPTION_SCOPE_TYPE', 'Magento\Core\Model\App', 'Magento_Core_Model_App::PARAM_RUN_TYPE'),
    array('INIT_OPTION_URIS', 'Magento\Core\Model\App'),
    array('INSTALLER_HOST_RESPONSE', 'Magento\Install\Model\Installer'),
    array(
        'LAYOUT_GENERAL_CACHE_TAG',
        'Magento\Core\Model\Layout\Merge',
        'Magento_Core_Model_Cache_Type_Layout::CACHE_TAG'
    ),
    array('LOCALE_CACHE_KEY', 'Magento\Backend\Block\Page\Footer'),
    array('LOCALE_CACHE_LIFETIME', 'Magento\Backend\Block\Page\Footer'),
    array('LOCALE_CACHE_TAG', 'Magento\Backend\Block\Page\Footer'),
    array('PARAM_CACHE_OPTIONS', '\Magento\Core\Model\App', '\Magento\Core\Model\App::PARAM_CACHE_FORCED_OPTIONS'),
    array('PATH_PREFIX_CUSTOMIZATION', 'Magento\Core\Model\Theme'),
    array('PATH_PREFIX_CUSTOMIZED', 'Magento\Core\Model\Theme\Files'),
    array('PUBLIC_BASE_THEME_DIR', 'Magento\Core\Model\Design\PackageInterface'),
    array('PUBLIC_CACHE_TAG', 'Magento\Core\Model\Design\PackageInterface'),
    array(
        'PUBLIC_MODULE_DIR',
        'Magento\Core\Model\Design\PackageInterface',
        'Magento_Core_Model_Design_Package::PUBLIC_MODULE_DIR'
    ),
    array('PUBLIC_MODULE_DIR', 'Magento\Framework\View\Publisher', 'Magento\Framework\View\Publisher\FileInterface::PUBLIC_MODULE_DIR'),
    array(
        'PUBLIC_THEME_DIR',
        'Magento\Core\Model\Design\PackageInterface',
        'Magento_Core_Model_Design_Package::PUBLIC_THEME_DIR'
    ),
    array('PUBLIC_THEME_DIR', 'Magento\Framework\View\Publisher', 'Magento\Framework\View\Publisher\FileInterface::PUBLIC_THEME_DIR'),
    array(
        'PUBLIC_VIEW_DIR',
        'Magento\Core\Model\Design\PackageInterface',
        'Magento_Core_Model_Design_Package::PUBLIC_VIEW_DIR'
    ),
    array('PUBLIC_VIEW_DIR', 'Magento\Framework\View\Publisher', 'Magento\Framework\View\Publisher\FileInterface::PUBLIC_VIEW_DIR'),
    array('REGISTRY_FORM_PARAMS_KEY', null, 'direct value'),
    array('RULE_PERM_ALLOW', '\Magento\Framework\Math\Random'),
    array('RULE_PERM_DENY', '\Magento\Framework\Math\Random'),
    array('RULE_PERM_INHERIT', '\Magento\Framework\Math\Random'),
    array('SCOPE_TYPE_GROUP', 'Magento\Core\Model\App', 'Magento\Store\Model\ScopeInterface::SCOPE_GROUP'),
    array('SCOPE_TYPE_STORE', 'Magento\Core\Model\App', 'Magento\Store\Model\ScopeInterface::SCOPE_STORE'),
    array(
        'SCOPE_TYPE_WEBSITE',
        'Magento\Core\Model\App',
        'Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE'
    ),
    array('SEESION_MAX_COOKIE_LIFETIME'),
    array('TYPE_BINARY', null, 'Magento_DB_Ddl_Table::TYPE_BLOB'),
    array('TYPE_CHAR', null, 'Magento_DB_Ddl_Table::TYPE_TEXT'),
    array('TYPE_CLOB', null, 'Magento_DB_Ddl_Table::TYPE_TEXT'),
    array('TYPE_DOUBLE', null, 'Magento_DB_Ddl_Table::TYPE_FLOAT'),
    array('TYPE_LONGVARBINARY', null, 'Magento_DB_Ddl_Table::TYPE_BLOB'),
    array('TYPE_LONGVARCHAR', null, 'Magento_DB_Ddl_Table::TYPE_TEXT'),
    array('TYPE_REAL', null, 'Magento_DB_Ddl_Table::TYPE_FLOAT'),
    array('TYPE_TIME', null, 'Magento_DB_Ddl_Table::TYPE_TIMESTAMP'),
    array('TYPE_TINYINT', null, 'Magento_DB_Ddl_Table::TYPE_SMALLINT'),
    array('TYPE_VARCHAR', null, 'Magento_DB_Ddl_Table::TYPE_TEXT'),
    array('URL_TYPE_SKIN'),
    array('URL_TYPE_CACHE'),
    array('XML_NODE_PAGE_TEMPLATE_FILTER', 'Magento\Cms\Helper\Data'),
    array('XML_NODE_BLOCK_TEMPLATE_FILTER', 'Magento\Cms\Helper\Data'),
    array('XML_NODE_RELATED_CACHE', 'Magento\Widget\Model\Widget\Instance'),
    array('XML_CHARSET_NODE', 'Magento\SalesRule\Helper\Coupon'),
    array('XML_CHARSET_SEPARATOR', 'Magento\SalesRule\Helper\Coupon'),
    array('XML_NODE_RELATED_CACHE', 'Magento\CatalogRule\Model\Rule'),
    array(
        'XML_NODE_ATTRIBUTE_NODES',
        'Magento\Catalog\Model\Resource\Product\Flat\Indexer',
        'XML_NODE_ATTRIBUTE_GROUPS'
    ),
    array(
        'XML_PATH_ALLOW_CURRENCIES',
        'Magento\Framework\Locale',
        'Magento_Core_Model_LocaleInterface::XML_PATH_ALLOW_CURRENCIES'
    ),
    array('XML_PATH_ALLOW_CODES', 'Magento\Framework\LocaleInterface'),
    array(
        'XML_PATH_ALLOW_DUPLICATION',
        'Magento\Core\Model\Design\PackageInterface',
        'Magento_Core_Model_Design_Package::XML_PATH_ALLOW_DUPLICATION'
    ),
    array('XML_PATH_ALLOW_MAP_UPDATE', 'Mage_Core_Model_Design_PackageInterface'),
    array('XML_PATH_BACKEND_FRONTNAME', 'Mage_Backend_Helper_Data'),
    array('XML_PATH_CACHE_BETA_TYPES'),
    array('XML_PATH_CHECK_EXTENSIONS', 'Magento\Install\Model\Config'),
    array('XML_PATH_CONNECTION_TYPE', 'Magento\Framework\App\Resource\Config'),
    array('XML_PATH_COUNTRY_DEFAULT', 'Magento\Paypal\Model\System\Config\Backend\MerchantCountry'),
    array(
        'XML_PATH_DEBUG_TEMPLATE_HINTS',
        'Magento\Framework\View\Element\Template',
        'Magento\Core\Model\TemplateEngine\Plugin::XML_PATH_DEBUG_TEMPLATE_HINTS'
    ),
    array(
        'XML_PATH_DEBUG_TEMPLATE_HINTS_BLOCKS',
        'Magento\Framework\View\Element\Template',
        'Magento\Core\Model\TemplateEngine\Plugin::XML_PATH_DEBUG_TEMPLATE_HINTS_BLOCKS'
    ),
    array('XML_PATH_DEFAULT_COUNTRY', 'Magento\Framework\Locale'),
    array('XML_PATH_DEFAULT_LOCALE', 'Magento\Framework\Locale', 'Magento_Core_Model_LocaleInterface::XML_PATH_DEFAULT_LOCALE'),
    array(
        'XML_PATH_DEFAULT_TIMEZONE',
        'Magento\Framework\Locale',
        'Magento_Core_Model_LocaleInterface::XML_PATH_DEFAULT_TIMEZONE'
    ),
    array('XML_PATH_INSTALL_DATE', 'Mage_Core_Model_App', 'Mage_Core_Model_Config_Primary::XML_PATH_INSTALL_DATE'),
    array('XML_PATH_LOCALE_INHERITANCE', 'Mage_Core_Model_Translate'),
    array('XML_PATH_PRODUCT_ATTRIBUTES', 'Magento\Wishlist\Model\Config'),
    array('XML_PATH_PRODUCT_COLLECTION_ATTRIBUTES', 'Magento\Catalog\Model\Config'),
    array('XML_PATH_QUOTE_PRODUCT_ATTRIBUTES', 'Magento\Sales\Model\Quote\Config'),
    array('XML_PATH_SENDING_SET_RETURN_PATH', 'Mage_Newsletter_Model_Subscriber'),
    array(
        'XML_PATH_SKIP_PROCESS_MODULES_UPDATES',
        'Mage_Core_Model_App',
        'Mage_Core_Model_Db_UpdaterInterface::XML_PATH_SKIP_PROCESS_MODULES_UPDATES'
    ),
    array(
        'XML_PATH_STATIC_FILE_SIGNATURE',
        'Magento\Core\Helper\Data',
        'Magento_Core_Model_Design_Package::XML_PATH_STATIC_FILE_SIGNATURE'
    ),
    array(
        'XML_PATH_STORE_ADDRESS1',
        'Magento\Shipping\Model\Shipping',
        'Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_ADDRESS1'
    ),
    array(
        'XML_PATH_STORE_ADDRESS2',
        'Magento\Shipping\Model\Shipping',
        'Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_ADDRESS2'
    ),
    array(
        'XML_PATH_STORE_CITY',
        'Magento\Shipping\Model\Shipping',
        'Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_CITY'
    ),
    array(
        'XML_PATH_STORE_REGION_ID',
        'Magento\Shipping\Model\Shipping',
        'Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_REGION_ID'
    ),
    array(
        'XML_PATH_STORE_ZIP',
        'Magento\Shipping\Model\Shipping',
        'Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_ZIP'
    ),
    array(
        'XML_PATH_STORE_COUNTRY_ID',
        'Magento\Shipping\Model\Shipping',
        'Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_COUNTRY_ID'
    ),
    array('XML_PATH_TEMPLATE_EMAIL', 'Magento\Core\Model\Email\Template'),
    array(
        'XML_PATH_TEMPLATE_FILTER',
        'Magento\Newsletter\Helper\Data',
        'Use directly model \Magento\Newsletter\Model\Template\Filter'
    ),
    array(
        'XML_PATH_THEME',
        'Magento\Core\Model\Design\PackageInterface',
        'Magento_Core_Model_Design_Package::XML_PATH_THEME'
    ),
    array(
        'XML_PATH_THEME_ID',
        'Magento\Core\Model\Design\PackageInterface',
        'Magento_Core_Model_Design_Package::XML_PATH_THEME_ID'
    ),
    array('XML_STORE_ROUTERS_PATH', 'Mage_Core_Controller_Varien_Front'),
    array('XML_PATH_SESSION_MESSAGE_MODELS', 'Magento\Catalog\Helper\Product\View'),
    array(
        'VALIDATOR_KEY',
        'Magento\Core\Model\Session\AbstractSession',
        'Magento_Core_Model_Session_Validator::VALIDATOR_KEY'
    ),
    array(
        'VALIDATOR_HTTP_USER_AGENT_KEY',
        'Magento\Core\Model\Session\AbstractSession',
        'Magento_Core_Model_Session_Validator::VALIDATOR_HTTP_USER_AGENT_KEY'
    ),
    array(
        'VALIDATOR_HTTP_X_FORVARDED_FOR_KEY',
        'Magento\Core\Model\Session\AbstractSession',
        'Magento_Core_Model_Session_Validator::VALIDATOR_HTTP_X_FORWARDED_FOR_KEY'
    ),
    array(
        'VALIDATOR_HTTP_VIA_KEY',
        'Magento\Core\Model\Session\AbstractSession',
        'Magento_Core_Model_Session_Validator::VALIDATOR_HTTP_VIA_KEY'
    ),
    array(
        'VALIDATOR_REMOTE_ADDR_KEY',
        'Magento\Core\Model\Session\AbstractSession',
        'Magento_Core_Model_Session_Validator::VALIDATOR_REMOTE_ADDR_KEY'
    ),
    array(
        'XML_PATH_USE_REMOTE_ADDR',
        'Magento\Core\Model\Session\AbstractSession',
        'Magento_Core_Model_Session_Validator::XML_PATH_USE_REMOTE_ADDR'
    ),
    array(
        'XML_PATH_USE_HTTP_VIA',
        'Magento\Core\Model\Session\AbstractSession',
        'Magento_Core_Model_Session_Validator::XML_PATH_USE_HTTP_VIA'
    ),
    array(
        'XML_PATH_USE_X_FORWARDED',
        'Magento\Core\Model\Session\AbstractSession',
        'Magento_Core_Model_Session_Validator::XML_PATH_USE_X_FORWARDED'
    ),
    array(
        'XML_PATH_USE_USER_AGENT',
        'Magento\Core\Model\Session\AbstractSession',
        'Magento_Core_Model_Session_Validator::XML_PATH_USE_USER_AGENT'
    ),
    array('XML_NODE_DIRECT_FRONT_NAMES', 'Magento\Framework\App\Request\Http'),
    array('XML_NODE_USET_AGENT_SKIP', 'Magento\Core\Model\Session\AbstractSession'),
    array('XML_PAGE_TYPE_RENDER_INHERITED', 'Magento\Framework\App\Action\Action'),
    array('XML_PATH_ALLOW_MAP_UPDATE', 'Magento\Core\Model\Design\FileResolution\StrategyPool'),
    array('XML_PATH_WEBAPI_REQUEST_INTERPRETERS', 'Magento\Webapi\Controller\Request\Rest\Interpreter\Factory'),
    array('XML_PATH_WEBAPI_RESPONSE_RENDERS', 'Magento\Webapi\Controller\Response\Rest\Renderer\Factor'),
    array('XML_PATH_THEME'),
    array('XML_PATH_ALLOW_DUPLICATION'),
    array('XML_NODE_BUNDLE_PRODUCT_TYPE', 'Magento\Bundle\Helper\Data'),
    array('XML_PATH_CONFIGURABLE_ALLOWED_TYPES', 'Magento\Catalog\Helper\Product\Configuration'),
    array('XML_PATH_GROUPED_ALLOWED_PRODUCT_TYPES', 'Magento\Catalog\Model\Config'),
    array('PRODUCT_OPTIONS_GROUPS_PATH', 'Magento\Catalog\Model\Config\Source\Product\Options\Type'),
    array('XML_NODE_ADD_FILTERABLE_ATTRIBUTES', 'Magento\Catalog\Helper\Product\Flat'),
    array('XML_NODE_ADD_CHILD_DATA', 'Magento\Catalog\Helper\Product\Flat'),
    array('XML_PATH_CONTENT_TEMPLATE_FILTER', 'Magento\Catalog\Helper\Data'),
    array('XML_NODE_MAX_INDEX_COUNT', 'Magento\Catalog\Model\Resource\Product\Flat\Indexer'),
    array('XML_NODE_ATTRIBUTE_GROUPS', 'Magento\Catalog\Model\Resource\Product\Flat\Indexer'),
    array('XML_PATH_UNASSIGNABLE_ATTRIBUTES', 'Magento\Catalog\Helper\Product'),
    array('XML_PATH_ATTRIBUTES_USED_IN_AUTOGENERATION', 'Magento\Catalog\Helper\Product'),
    array('XML_PATH_PRODUCT_TYPE_SWITCHER_LABEL', 'Magento\Catalog\Helper\Product'),
    array('CONFIG_KEY_ENTITIES', 'Magento\ImportExport\Model\Export'),
    array('CONFIG_KEY_FORMATS', 'Magento\ImportExport\Model\Export'),
    array('CONFIG_KEY_ENTITIES', 'Magento\ImportExport\Model\Import'),
    array('REGEX_RUN_MODEL', 'Magento\Cron\Model\Observer'),
    array('XML_PATH_FRONT_NAME', 'Magento\DesignEditor\Helper\Data'),
    array('XML_PATH_DISABLED_CACHE_TYPES', 'Magento\DesignEditor\Helper\Data'),
    array('XML_PATH_ENCRYPTION_MODEL', 'Magento\Core\Helper\Data'),
    array('CONFIG_KEY_PATH_TO_MAP_FILE', 'Magento\Framework\Module\Setup\Migration'),
    array('XML_PATH_SKIP_PROCESS_MODULES_UPDATES', 'Magento\Framework\App\UpdaterInterface'),
    array('XML_PATH_IGNORE_DEV_MODE', 'Magento\Framework\Module\UpdaterInterface'),
    array('XML_PATH_SKIP_PROCESS_MODULES_UPDATES', 'Magento\Framework\Module\UpdaterInterface'),
    array('XML_PATH_USE_CUSTOM_ADMIN_PATH', 'Magento\Backend\Helper\Data'),
    array('XML_PATH_CUSTOM_ADMIN_PATH', 'Magento\Backend\Helper\Data'),
    array('XML_PATH_BACKEND_AREA_FRONTNAME', 'Magento\Backend\Helper\Data'),
    array('PARAM_BACKEND_FRONT_NAME', 'Magento\Backend\Helper\Data'),
    array('CHARS_LOWERS', '\Magento\Core\Helper\Data', '\Magento\Framework\Math\Random::CHARS_LOWERS'),
    array('CHARS_UPPERS', '\Magento\Core\Helper\Data', '\Magento\Framework\Math\Random::CHARS_UPPERS'),
    array('CHARS_DIGITS', '\Magento\Core\Helper\Data', '\Magento\Framework\Math\Random::CHARS_DIGITS'),
    array('CHARS_SPECIALS', '\Magento\Core\Helper\Data'),
    array('CHARS_SPECIALS', '\Magento\Framework\Math\Random'),
    array('CHARS_PASSWORD_LOWERS', '\Magento\Core\Helper\Data'),
    array('CHARS_PASSWORD_LOWERS', '\Magento\Framework\Math\Random'),
    array('CHARS_PASSWORD_UPPERS', '\Magento\Core\Helper\Data'),
    array('CHARS_PASSWORD_UPPERS', '\Magento\Framework\Math\Random'),
    array('CHARS_PASSWORD_DIGITS', '\Magento\Core\Helper\Data'),
    array('CHARS_PASSWORD_DIGITS', '\Magento\Framework\Math\Random'),
    array('CHARS_PASSWORD_SPECIALS', '\Magento\Core\Helper\Data'),
    array('CHARS_PASSWORD_SPECIALS', '\Magento\Framework\Math\Random'),
    array('XML_NODE_REMOTE_ADDR_HEADERS', '\Magento\Core\Helper\Http'),
    array(
        'XML_PATH_EU_COUNTRIES_LIST',
        '\Magento\Core\Helper\Data',
        'Magento\Customer\Helper\Data::XML_PATH_EU_COUNTRIES_LIST'
    ),
    array(
        'XML_PATH_MERCHANT_COUNTRY_CODE',
        '\Magento\Core\Helper\Data',
        'Magento\Customer\Helper\Data::XML_PATH_MERCHANT_COUNTRY_CODE'
    ),
    array(
        'XML_PATH_MERCHANT_VAT_NUMBER',
        '\Magento\Core\Helper\Data',
        'Magento\Customer\Helper\Data::XML_PATH_MERCHANT_VAT_NUMBER'
    ),
    array(
        'XML_PATH_PROTECTED_FILE_EXTENSIONS',
        '\Magento\Core\Helper\Data',
        '\Magento\Core\Model\File\Validator\NotProtectedExtension::XML_PATH_PROTECTED_FILE_EXTENSIONS'
    ),
    array(
        'XML_PATH_PUBLIC_FILES_VALID_PATHS',
        '\Magento\Core\Helper\Data',
        '\Magento\Catalog\Helper\Catalog::XML_PATH_PUBLIC_FILES_VALID_PATHS'
    ),
    array(
        'XML_PATH_PUBLIC_FILES_VALID_PATHS',
        'Magento\Catalog\Helper\Catalog',
        '\Magento\Sitemap\Helper\Data::XML_PATH_PUBLIC_FILES_VALID_PATHS'
    ),
    array(
        'XML_PATH_SITEMAP_VALID_PATHS',
        '\Magento\Catalog\Helper\Catalog',
        '\Magento\Sitemap\Helper\Data::XML_PATH_SITEMAP_VALID_PATHS'
    ),
    array('TYPE_PHYSICAL', '\Magento\Core\Model\Theme', '\Magento\Framework\View\Design\ThemeInterface::TYPE_PHYSICAL'),
    array('TYPE_VIRTUAL', '\Magento\Core\Model\Theme', '\Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL'),
    array('TYPE_STAGING', '\Magento\Core\Model\Theme', '\Magento\Framework\View\Design\ThemeInterface::TYPE_STAGING'),
    array('PATH_SEPARATOR', '\Magento\Core\Model\Theme', '\Magento\Framework\View\Design\ThemeInterface::PATH_SEPARATOR'),
    array('CODE_SEPARATOR', '\Magento\Core\Model\Theme', '\Magento\Framework\View\Design\ThemeInterface::CODE_SEPARATOR'),
    array(
        'XML_PATH_IMAGE_ADAPTER',
        '\Magento\Core\Model\Image\AdapterFactory',
        '\Magento\Core\Model\Image\Adapter\Config::XML_PATH_IMAGE_ADAPTER'
    ),
    array(
        'ADAPTER_IM',
        '\Magento\Core\Model\Image\AdapterFactory',
        '\Magento\Framework\Image\Adapter\AdapterInterface::ADAPTER_IM'
    ),
    array(
        'ADAPTER_GD2',
        '\Magento\Core\Model\Image\AdapterFactory',
        '\Magento\Framework\Image\Adapter\AdapterInterface::ADAPTER_GD2'
    ),
    array('XML_PATH_IMAGE_TYPES', 'Magento\Adminhtml\Block\Catalog\Product\Frontend\Product\Watermark'),
    array('XML_PATH_WEBHOOK', 'Magento\Webhook\Model\Source\Hook'),
    array('XML_PATH_SUBSCRIPTIONS', 'Magento\Webhook\Model\Subscription\Config'),
    array('PAYMENT_INFO_TRANSPORT_SHIPPING_OVERRIDEN', 'Magento\Paypal\Model\Express\Checkout'),
    array(
        'XML_PATH_USE_FRONTEND_SID',
        '\Magento\Core\Model\Session\AbstractSession',
        '\Magento\Framework\Session\SidResolver::XML_PATH_USE_FRONTEND_SID'
    ),
    array(
        'SESSION_ID_QUERY_PARAM',
        '\Magento\Core\Model\Session\AbstractSession',
        '\Magento\Framework\Session\SidResolverInterface::SESSION_ID_QUERY_PARAM'
    ),
    array(
        'XML_PATH_COOKIE_DOMAIN',
        '\Magento\Framework\Stdlib\Cookie',
        '\Magento\Core\Model\Session\Config::XML_PATH_COOKIE_DOMAIN'
    ),
    array(
        'XML_PATH_COOKIE_PATH',
        '\Magento\Framework\Stdlib\Cookie',
        '\Magento\Core\Model\Session\Config::XML_PATH_COOKIE_PATH'
    ),
    array(
        'XML_PATH_COOKIE_LIFETIME',
        '\Magento\Framework\Stdlib\Cookie',
        '\Magento\Core\Model\Session\Config::XML_PATH_COOKIE_LIFETIME'
    ),
    array(
        'XML_PATH_COOKIE_HTTPONLY',
        '\Magento\Framework\Stdlib\Cookie',
        '\Magento\Core\Model\Session\Config::XML_PATH_COOKIE_HTTPONLY'
    ),
    array(
        'PARAM_SESSION_SAVE_METHOD',
        '\Magento\Core\Model\Session\AbstractSession',
        '\Magento\Core\Model\Session\Config::PARAM_SESSION_SAVE_METHOD'
    ),
    array(
        'PARAM_SESSION_SAVE_PATH',
        '\Magento\Core\Model\Session\AbstractSession',
        '\Magento\Core\Model\Session\Config::PARAM_SESSION_SAVE_PATH'
    ),
    array(
        'PARAM_SESSION_CACHE_LIMITER',
        '\Magento\Core\Model\Session\AbstractSession',
        '\Magento\Core\Model\Session\Config::PARAM_SESSION_CACHE_LIMITER'
    ),
    array(
        'XML_NODE_SESSION_SAVE_PATH',
        'Magento\Core\Model\Session\AbstractSession',
        'Magento\Core\Model\Session\Config::PARAM_SESSION_SAVE_PATH'
    ),
    array(
        'XML_NODE_SESSION_SAVE',
        'Magento\Core\Model\Session\AbstractSession',
        'Magento\Core\Model\Session\Config::PARAM_SESSION_SAVE_METHOD'
    ),
    array('XML_PATH_LOG_EXCEPTION_FILE', 'Magento\Core\Model\Session\AbstractSession'),
    array(
        'XML_PATH_ROBOTS_DEFAULT_CUSTOM_INSTRUCTIONS',
        'Magento\Theme\Helper\Robots',
        'Magento\Backend\Block\Page\System\Config\Robots::XML_PATH_ROBOTS_DEFAULT_CUSTOM_INSTRUCTIONS'
    ),
    array(
        'XML_PATH_MERGE_CSS_FILES',
        'Magento\Framework\View\Asset\MergeService',
        'Magento\Core\Model\Asset\Config::XML_PATH_MERGE_CSS_FILES'
    ),
    array(
        'XML_PATH_MERGE_JS_FILES',
        'Magento\Framework\View\Asset\MergeService',
        'Magento\Core\Model\Asset\Config::XML_PATH_MERGE_JS_FILES'
    ),
    array(
        'XML_PATH_MINIFICATION_ENABLED',
        'Magento\Framework\View\Asset\MinifyService',
        'Magento\Core\Model\Asset\Config::XML_PATH_MINIFICATION_ENABLED'
    ),
    array(
        'XML_PATH_MINIFICATION_ADAPTER',
        'Magento\Framework\View\Asset\MinifyService',
        'Magento\Core\Model\Asset\Config::XML_PATH_MINIFICATION_ADAPTER'
    ),
    array(
        'USE_PARENT_IMAGE',
        'Magento\ConfigurableProduct\Block\Cart\Item\Renderer\Configurable',
        'Magento\Catalog\Model\Config\Source\Product\Thumbnail::OPTION_USE_PARENT_IMAGE'
    ),
    array(
        'USE_PARENT_IMAGE',
        'Magento\GroupedProduct\Block\Cart\Item\Renderer\Grouped',
        'Magento\Catalog\Model\Config\Source\Product\Thumbnail::OPTION_USE_PARENT_IMAGE'
    ),
    array(
        'CONFIGURABLE_PRODUCT_IMAGE',
        'Magento\ConfigurableProduct\Block\Cart\Item\Renderer\Configurable',
        'Magento\ConfigurableProduct\Block\Cart\Item\Renderer\Configurable::CONFIG_THUMBNAIL_SOURCE'
    ),
    array(
        'GROUPED_PRODUCT_IMAGE',
        'Magento\GroupedProduct\Block\Cart\Item\Renderer\Grouped',
        'Magento\GroupedProduct\Block\Cart\Item\Renderer\Grouped::CONFIG_THUMBNAIL_SOURCE'
    ),
    array('TYPE_BLOCK', 'Magento\Framework\View\Layout', '\Magento\Framework\View\Layout\Element'),
    array('TYPE_CONTAINER', 'Magento\Framework\View\Layout', '\Magento\Framework\View\Layout\Element'),
    array('TYPE_ACTION', 'Magento\Framework\View\Layout', '\Magento\Framework\View\Layout\Element'),
    array('TYPE_ARGUMENTS', 'Magento\Framework\View\Layout', '\Magento\Framework\View\Layout\Element'),
    array('TYPE_ARGUMENT', 'Magento\Framework\View\Layout', '\Magento\Framework\View\Layout\Element'),
    array('TYPE_REFERENCE_BLOCK', 'Magento\Framework\View\Layout', '\Magento\Framework\View\Layout\Element'),
    array('TYPE_REFERENCE_CONTAINER', 'Magento\Framework\View\Layout', '\Magento\Framework\View\Layout\Element'),
    array('TYPE_REMOVE', 'Magento\Framework\View\Layout', '\Magento\Framework\View\Layout\Element'),
    array('TYPE_MOVE', 'Magento\Framework\View\Layout', '\Magento\Framework\View\Layout\Element'),
    array('CONTAINER_OPT_HTML_TAG', 'Magento\Framework\View\Layout', '\Magento\Framework\View\Layout\Element'),
    array('CONTAINER_OPT_HTML_CLASS', 'Magento\Framework\View\Layout', '\Magento\Framework\View\Layout\Element'),
    array('CONTAINER_OPT_HTML_ID', 'Magento\Framework\View\Layout', '\Magento\Framework\View\Layout\Element'),
    array('CONTAINER_OPT_LABEL', 'Magento\Framework\View\Layout', '\Magento\Framework\View\Layout\Element'),
    array('XML_PATH_THEME_ID', 'Magento\Core\Model\View\Design', '\Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID'),
    array('UPLOAD_ROOT', 'Magento\Backend\Model\Config\Backend\Logo'),
    array('UPLOAD_ROOT', 'Magento\Backend\Model\Config\Backend\Favicon'),
    array('DIRECTORY_SEPARATOR', 'Magento\Framework\Filesystem'),
    array(
        'MAX_QTY_VALUE',
        '\Magento\Catalog\Controller\Adminhtml\Product',
        'Magento\Catalog\Controller\Adminhtml\Product\Initialization\StockDataFilter::MAX_QTY_VALUE'
    ),
    array(
        'LINK_TYPE_GROUPED',
        '\Magento\Catalog\Model\Product\Link',
        '\Magento\GroupedProduct\Model\Resource\Product\Link::LINK_TYPE_GROUPED'
    ),
    array(
        'TYPE_GROUPED',
        '\Magento\Catalog\Model\Product\Type',
        '\Magento\GroupedProduct\Model\Resource\Product\Link::LINK_TYPE_GROUPED'
    ),
    array('PARAM_APP_URIS', 'Magento\Framework\Filesystem'),
    array('ROOT', '\Magento\Framework\Filesystem', '\Magento\Framework\App\Filesystem::ROOT_DIR'),
    array('APP', '\Magento\Framework\Filesystem', '\Magento\Framework\App\Filesystem::APP_DIR'),
    array('MODULES', '\Magento\Framework\Filesystem', '\Magento\Framework\App\Filesystem::MODULES_DIR'),
    array('THEMES', '\Magento\Framework\Filesystem', '\Magento\Framework\App\Filesystem::THEMES_DIR'),
    array('CONFIG', '\Magento\Framework\Filesystem', '\Magento\Framework\App\Filesystem::CONFIG_DIR'),
    array('LIB', '\Magento\Framework\Filesystem', '\Magento\Framework\App\Filesystem::LIB_INTERNAL'),
    array('LOCALE', '\Magento\Framework\Filesystem', '\Magento\Framework\App\Filesystem::LOCALE_DIR'),
    array('PUB', '\Magento\Framework\Filesystem', '\Magento\Framework\App\Filesystem::PUB_DIR'),
    array('PUB_LIB', '\Magento\Framework\Filesystem', '\Magento\Framework\App\Filesystem::LIB_WEB'),
    array('PUB_LIB_DIR', '\Magento\Framework\App\Filesystem', '\Magento\Framework\App\Filesystem::LIB_WEB'),
    array('MEDIA', '\Magento\Framework\Filesystem', '\Magento\Framework\App\Filesystem::MEDIA_DIR'),
    array('STATIC_VIEW', '\Magento\Framework\Filesystem', '\Magento\Framework\App\Filesystem::STATIC_VIEW_DIR'),
    array('PUB_VIEW_CACHE', '\Magento\Framework\Filesystem', '\Magento\Framework\App\Filesystem::STATIC_VIEW_DIR'),
    array('PUB_VIEW_CACHE_DIR', '\Magento\Framework\App\Filesystem', '\Magento\Framework\App\Filesystem::STATIC_VIEW_DIR'),
    array('VAR_DIR', '\Magento\Framework\Filesystem', '\Magento\Framework\App\Filesystem'),
    array('TMP', '\Magento\Framework\Filesystem', '\Magento\Framework\App\Filesystem::TMP_DIR'),
    array('CACHE', '\Magento\Framework\Filesystem', '\Magento\Framework\App\Filesystem::CACHE_DIR'),
    array('LOG', '\Magento\Framework\Filesystem', '\Magento\Framework\App\Filesystem::LOG_DIR'),
    array('SESSION', '\Magento\Framework\Filesystem', '\Magento\Framework\App\Filesystem::SESSION_DIR'),
    array('DI', '\Magento\Framework\Filesystem', '\Magento\Framework\App\Filesystem::DI_DIR'),
    array('GENERATION', '\Magento\Framework\Filesystem', '\Magento\Framework\App\Filesystem::GENERATION_DIR'),
    array('UPLOAD', '\Magento\Framework\Filesystem', '\Magento\Framework\App\Filesystem::UPLOAD_DIR'),
    array('SYS_TMP', '\Magento\Framework\Filesystem', '\Magento\Framework\App\Filesystem::SYS_TMP_DIR'),
    array('LAYOUT_NAVIGATION_CLASS_NAME', 'Magento\DesignEditor\Model\State'),
    array(
        'TYPE_CONFIGURABLE',
        '\Magento\Catalog\Model\Product\Type',
        '\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE'
    ),
    array('LIB_DIR', '\Magento\Framework\App\Filesystem', '\Magento\Framework\App\Filesystem::LIB_INTERNAL'),
    array(
        'PERIOD_UNIT_DAY',
        '\Magento\Payment\Model\Recurring\Profile',
        '\Magento\RecurringPayment\Model\PeriodUnits::DAY'
    ),
    array(
        'PERIOD_UNIT_WEEK',
        '\Magento\Payment\Model\Recurring\Profile',
        '\Magento\RecurringPayment\Model\PeriodUnits::WEEK'
    ),
    array(
        'PERIOD_UNIT_SEMI_MONTH',
        '\Magento\Payment\Model\Recurring\Profile',
        '\Magento\RecurringPayment\Model\PeriodUnits::SEMI_MONTH'
    ),
    array(
        'PERIOD_UNIT_MONTH',
        '\Magento\Payment\Model\Recurring\Profile',
        '\Magento\RecurringPayment\Model\PeriodUnits::MONTH'
    ),
    array(
        'PERIOD_UNIT_YEAR',
        '\Magento\Payment\Model\Recurring\Profile',
        '\Magento\RecurringPayment\Model\PeriodUnits::YEAR'
    ),
    array(
        'XML_PATH_IS_ENABLED_FLAT_CATALOG_CATEGORY',
        '\Magento\Catalog\Helper\Category\Flat',
        '\Magento\Catalog\Model\Indexer\Category\Flat\Config::XML_PATH_IS_ENABLED_FLAT_CATALOG_CATEGORY'
    ),
    array('CSV_SEPARATOR', 'Magento\Framework\Translate'),
    array('SCOPE_SEPARATOR', 'Magento\Framework\Translate'),
    array('CONFIG_KEY_AREA', 'Magento\Framework\Translate'),
    array('CONFIG_KEY_LOCALE', 'Magento\Framework\Translate'),
    array('CONFIG_KEY_SCOPE', 'Magento\Framework\Translate'),
    array('CONFIG_KEY_DESIGN_THEME', 'Magento\Framework\Translate'),
    array(
        'XML_NODE_MAX_INDEX_COUNT',
        'Magento\Catalog\Model\Indexer\Product\Flat\AbstractAction',
        'Magento\Catalog\Model\Indexer\Product\Flat\FlatTableBuilder'
    ),
    array(
        'ATTRIBUTES_CHUNK_SIZE',
        'Magento\Catalog\Model\Indexer\Product\Flat\AbstractAction',
        'Magento\Catalog\Model\Indexer\Product\Flat\Action\Indexer'
    ),
    array(
        'CACHE_CATEGORY_TAG',
        'Magento\Catalog\Model\Product',
        'Magento\Catalog\Model\Product::CACHE_PRODUCT_CATEGORY_TAG'
    ),
    array('XML_PATH_UNSECURE_BASE_LIB_URL'),
    array('XML_PATH_SECURE_BASE_LIB_URL'),
    array('XML_PATH_SECURE_BASE_CACHE_URL'),
    array('XML_PATH_UNSECURE_BASE_CACHE_URL'),
    array('PUBLIC_MERGE_DIR', '', '\Magento\Framework\View\Asset\Merged::getRelativeDir'),
    array('PUBLIC_MINIFY_DIR', '', '\Magento\Framework\View\Asset\MinifyService::getRelativeDir'),
    ['CONTENT_TYPE_CSS', 'Magento\Framework\View\Publisher'],
    ['CONTENT_TYPE_JS', 'Magento\Framework\View\Publisher'],
    ['CONTENT_TYPE_PHP', 'Magento\Framework\View\Publisher'],
    ['CONTENT_TYPE_PHTML', 'Magento\Framework\View\Publisher'],
    ['CONTENT_TYPE_XML', 'Magento\Framework\View\Publisher'],
);
