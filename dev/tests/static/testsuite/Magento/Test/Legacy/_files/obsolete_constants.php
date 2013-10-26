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
    array('CACHE_TAG', 'Magento\Api\Model\Config', 'Magento_Api_Model_Cache_Type::CACHE_TAG'),
    array('CACHE_TAG', 'Magento\Core\Model\AppInterface'),
    array('CACHE_TAG', 'Magento\Core\Model\Resource\Db\Collection\AbstractCollection',
        'Magento_Core_Model_Cache_Type_Collection::CACHE_TAG'
    ),
    array('CACHE_TAG', 'Magento\Core\Model\Translate', 'Magento_Core_Model_Cache_Type_Translate::CACHE_TAG'),
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
    array('DEFAULT_SETUP_RESOURCE', 'Mage_Core_Model_Resource',
        'Magento_Core_Model_Config_Resource::DEFAULT_SETUP_CONNECTION'
    ),
    array('DEFAULT_READ_RESOURCE', 'Mage_Core_Model_Resource',
        'Magento_Core_Model_Config_Resource::DEFAULT_READ_CONNECTION'
    ),
    array('DEFAULT_WRITE_RESOURCE', 'Mage_Core_Model_Resource',
        'Magento_Core_Model_Config_Resource::DEFAULT_WRITE_CONNECTION'
    ),
    array('DEFAULT_CURRENCY', 'Magento\Core\Model\Locale', 'Magento_Core_Model_LocaleInterface::DEFAULT_CURRENCY'),
    array('DEFAULT_ERROR_HANDLER', 'Magento\Core\Model\App'),
    array('DEFAULT_READ_CONNECTION', 'Magento\Core\Model\Config\Resource'),
    array('DEFAULT_WRITE_CONNECTION', 'Magento\Core\Model\Config\Resource'),
    array('DEFAULT_ERROR_HANDLER', 'Mage'),
    array('DEFAULT_LOCALE', 'Magento\Core\Model\Locale', 'Magento_Core_Model_LocaleInterface::DEFAULT_LOCALE'),
    array('DEFAULT_THEME_NAME', 'Magento\Core\Model\Design\PackageInterface'),
    array('DEFAULT_THEME_NAME', 'Magento\Core\Model\Design\Package'),
    array('DEFAULT_TIMEZONE', 'Magento\Core\Model\Locale', 'Magento_Core_Model_LocaleInterface::DEFAULT_TIMEZONE'),
    array('DEFAULT_VALUE_TABLE_PREFIX'),
    array('ENTITY_PRODUCT', 'Magento\Review\Model\Review'),
    array('EXCEPTION_CODE_IS_GROUPED_PRODUCT'),
    array('FALLBACK_MAP_DIR', 'Magento\Core\Model\Design\PackageInterface'),
    array('FORMAT_TYPE_FULL', 'Magento\Core\Model\Locale', 'Magento_Core_Model_LocaleInterface::FORMAT_TYPE_FULL'),
    array('FORMAT_TYPE_LONG', 'Magento\Core\Model\Locale', 'Magento_Core_Model_LocaleInterface::FORMAT_TYPE_LONG'),
    array('FORMAT_TYPE_MEDIUM', 'Magento\Core\Model\Locale', 'Magento_Core_Model_LocaleInterface::FORMAT_TYPE_MEDIUM'),
    array('FORMAT_TYPE_SHORT', 'Magento\Core\Model\Locale', 'Magento_Core_Model_LocaleInterface::FORMAT_TYPE_SHORT'),
    array('GALLERY_IMAGE_TABLE', 'Magento\Catalog\Model\Resource\Product\Attribute\Backend\Media'),
    array('HASH_ALGO'),
    array('INIT_OPTION_DIRS', 'Magento\Core\Model\App', 'Magento_Core_Model_App::PARAM_APP_DIRS'),
    array('INIT_OPTION_REQUEST', 'Magento\Core\Model\App'),
    array('INIT_OPTION_RESPONSE', 'Magento\Core\Model\App'),
    array('INIT_OPTION_SCOPE_CODE', 'Magento\Core\Model\App', 'Magento_Core_Model_App::PARAM_RUN_CODE'),
    array('INIT_OPTION_SCOPE_TYPE', 'Magento\Core\Model\App', 'Magento_Core_Model_App::PARAM_RUN_TYPE'),
    array('INIT_OPTION_URIS', 'Magento\Core\Model\App', 'Magento_Core_Model_App::PARAM_APP_URIS'),
    array('INSTALLER_HOST_RESPONSE', 'Magento\Install\Model\Installer'),
    array('LAYOUT_GENERAL_CACHE_TAG', 'Magento\Core\Model\Layout\Merge', 'Magento_Core_Model_Cache_Type_Layout::CACHE_TAG'),
    array('LOCALE_CACHE_KEY', 'Magento\Adminhtml\Block\Page\Footer'),
    array('LOCALE_CACHE_LIFETIME', 'Magento\Adminhtml\Block\Page\Footer'),
    array('LOCALE_CACHE_TAG', 'Magento\Adminhtml\Block\Page\Footer'),
    array('PATH_PREFIX_CUSTOMIZATION', 'Magento\Core\Model\Theme'),
    array('PATH_PREFIX_CUSTOMIZED', 'Magento\Core\Model\Theme\Files'),
    array('PUBLIC_BASE_THEME_DIR', 'Magento\Core\Model\Design\PackageInterface'),
    array('PUBLIC_CACHE_TAG', 'Magento\Core\Model\Design\PackageInterface'),
    array('PUBLIC_MODULE_DIR', 'Magento\Core\Model\Design\PackageInterface',
        'Magento_Core_Model_Design_Package::PUBLIC_MODULE_DIR'
    ),
    array('PUBLIC_THEME_DIR', 'Magento\Core\Model\Design\PackageInterface',
        'Magento_Core_Model_Design_Package::PUBLIC_THEME_DIR'
    ),
    array('PUBLIC_VIEW_DIR', 'Magento\Core\Model\Design\PackageInterface',
        'Magento_Core_Model_Design_Package::PUBLIC_VIEW_DIR'
    ),
    array('REGISTRY_FORM_PARAMS_KEY', null, 'direct value'),
    array('SCOPE_TYPE_GROUP', 'Magento\Core\Model\App', 'Magento_Core_Model_StoreManagerInterface::SCOPE_TYPE_GROUP'),
    array('SCOPE_TYPE_STORE', 'Magento\Core\Model\App', 'Magento_Core_Model_StoreManagerInterface::SCOPE_TYPE_STORE'),
    array('SCOPE_TYPE_WEBSITE', 'Magento\Core\Model\App', 'Magento_Core_Model_StoreManagerInterface::SCOPE_TYPE_WEBSITE'),
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
    array('XML_NODE_PAGE_TEMPLATE_FILTER', 'Magento\Cms\Helper\Data'),
    array('XML_NODE_BLOCK_TEMPLATE_FILTER', 'Magento\Cms\Helper\Data'),
    array('XML_NODE_RELATED_CACHE', 'Magento\Widget\Model\Widget\Instance'),
    array('XML_CHARSET_NODE', 'Magento\SalesRule\Helper\Coupon'),
    array('XML_CHARSET_SEPARATOR', 'Magento\SalesRule\Helper\Coupon'),
    array('XML_NODE_RELATED_CACHE', 'Magento\CatalogRule\Model\Rule'),
    array('XML_NODE_ATTRIBUTE_NODES', 'Magento\Catalog\Model\Resource\Product\Flat\Indexer',
        'XML_NODE_ATTRIBUTE_GROUPS'),
    array('XML_PATH_ALLOW_CODES', 'Magento\Core\Model\Locale', 'Magento_Core_Model_LocaleInterface::XML_PATH_ALLOW_CODES'),
    array('XML_PATH_ALLOW_CURRENCIES', 'Magento\Core\Model\Locale',
        'Magento_Core_Model_LocaleInterface::XML_PATH_ALLOW_CURRENCIES'
    ),
    array('XML_PATH_ALLOW_CURRENCIES_INSTALLED', 'Magento\Core\Model\Locale',
        'Magento_Core_Model_LocaleInterface::XML_PATH_ALLOW_CURRENCIES_INSTALLED'),
    array('XML_PATH_ALLOW_DUPLICATION', 'Magento\Core\Model\Design\PackageInterface',
        'Magento_Core_Model_Design_Package::XML_PATH_ALLOW_DUPLICATION'
    ),
    array('XML_PATH_ALLOW_MAP_UPDATE', 'Mage_Core_Model_Design_PackageInterface'),
    array('XML_PATH_BACKEND_FRONTNAME', 'Mage_Backend_Helper_Data'),
    array('XML_PATH_CACHE_BETA_TYPES'),
    array('XML_PATH_CHECK_EXTENSIONS', 'Magento\Install\Model\Config'),
    array('XML_PATH_CONNECTION_TYPE', 'Magento\Core\Model\Config\Resource'),
    array('XML_PATH_COUNTRY_DEFAULT', 'Magento\Paypal\Model\System\Config\Backend\MerchantCountry'),
    array('XML_PATH_DEBUG_TEMPLATE_HINTS', 'Magento\Core\Block\Template',
        'Magento\Core\Model\TemplateEngine\Plugin::XML_PATH_DEBUG_TEMPLATE_HINTS'),
    array('XML_PATH_DEBUG_TEMPLATE_HINTS_BLOCKS', 'Magento\Core\Block\Template',
        'Magento\Core\Model\TemplateEngine\Plugin::XML_PATH_DEBUG_TEMPLATE_HINTS_BLOCKS'),
    array('XML_PATH_DEFAULT_COUNTRY', 'Magento\Core\Model\Locale'),
    array('XML_PATH_DEFAULT_LOCALE', 'Magento\Core\Model\Locale',
        'Magento_Core_Model_LocaleInterface::XML_PATH_DEFAULT_LOCALE'
    ),
    array('XML_PATH_DEFAULT_TIMEZONE', 'Magento\Core\Model\Locale',
        'Magento_Core_Model_LocaleInterface::XML_PATH_DEFAULT_TIMEZONE'
    ),
    array('XML_PATH_INDEXER_DATA', 'Magento\Index\Model\Process'),
    array('XML_PATH_INSTALL_DATE', 'Mage_Core_Model_App', 'Mage_Core_Model_Config_Primary::XML_PATH_INSTALL_DATE'),
    array('XML_PATH_EXTERNAL_CACHE_CONTROLS', 'Magento\PageCache\Helper\Data'),
    array('XML_PATH_EXTERNAL_CACHE_CONTROL', 'Magento\PageCache\Helper\Data'),
    array('XML_PATH_LOCALE_INHERITANCE', 'Mage_Core_Model_Translate'),
    array('XML_PATH_PRODUCT_ATTRIBUTES', 'Magento\Wishlist\Model\Config'),
    array('XML_PATH_PRODUCT_COLLECTION_ATTRIBUTES', 'Magento\Catalog\Model\Config'),
    array('XML_PATH_QUOTE_PRODUCT_ATTRIBUTES', 'Magento\Sales\Model\Quote\Config'),
    array('XML_PATH_SENDING_SET_RETURN_PATH', 'Mage_Newsletter_Model_Subscriber'),
    array('XML_PATH_SKIP_PROCESS_MODULES_UPDATES', 'Mage_Core_Model_App',
        'Mage_Core_Model_Db_UpdaterInterface::XML_PATH_SKIP_PROCESS_MODULES_UPDATES'
    ),
    array('XML_PATH_STATIC_FILE_SIGNATURE', 'Magento\Core\Helper\Data',
        'Magento_Core_Model_Design_Package::XML_PATH_STATIC_FILE_SIGNATURE'
    ),
    array('XML_PATH_TEMPLATE_EMAIL', 'Magento\Core\Model\Email\Template'),
    array('XML_PATH_TEMPLATE_FILTER', 'Magento\Newsletter\Helper\Data',
        'Use directly model \Magento\Newsletter\Model\Template\Filter'
    ),
    array('XML_PATH_THEME', 'Magento\Core\Model\Design\PackageInterface',
        'Magento_Core_Model_Design_Package::XML_PATH_THEME'
    ),
    array('XML_PATH_THEME_ID', 'Magento\Core\Model\Design\PackageInterface',
        'Magento_Core_Model_Design_Package::XML_PATH_THEME_ID'
    ),
    array('XML_STORE_ROUTERS_PATH', 'Mage_Core_Controller_Varien_Front'),
    array('XML_PATH_SESSION_MESSAGE_MODELS', 'Magento\Catalog\Helper\Product\View'),

    array('VALIDATOR_KEY', 'Magento\Core\Model\Session\AbstractSession',
        'Magento_Core_Model_Session_Validator::VALIDATOR_KEY'
    ),
    array('VALIDATOR_HTTP_USER_AGENT_KEY', 'Magento\Core\Model\Session\AbstractSession',
        'Magento_Core_Model_Session_Validator::VALIDATOR_HTTP_USER_AGENT_KEY'
    ),
    array('VALIDATOR_HTTP_X_FORVARDED_FOR_KEY', 'Magento\Core\Model\Session\AbstractSession',
        'Magento_Core_Model_Session_Validator::VALIDATOR_HTTP_X_FORWARDED_FOR_KEY'
    ),
    array('VALIDATOR_HTTP_VIA_KEY', 'Magento\Core\Model\Session\AbstractSession',
        'Magento_Core_Model_Session_Validator::VALIDATOR_HTTP_VIA_KEY'
    ),
    array('VALIDATOR_REMOTE_ADDR_KEY', 'Magento\Core\Model\Session\AbstractSession',
        'Magento_Core_Model_Session_Validator::VALIDATOR_REMOTE_ADDR_KEY'
    ),
    array('XML_PATH_USE_REMOTE_ADDR', 'Magento\Core\Model\Session\AbstractSession',
        'Magento_Core_Model_Session_Validator::XML_PATH_USE_REMOTE_ADDR'
    ),
    array('XML_PATH_USE_HTTP_VIA', 'Magento\Core\Model\Session\AbstractSession',
        'Magento_Core_Model_Session_Validator::XML_PATH_USE_HTTP_VIA'
    ),
    array('XML_PATH_USE_X_FORWARDED', 'Magento\Core\Model\Session\AbstractSession',
        'Magento_Core_Model_Session_Validator::XML_PATH_USE_X_FORWARDED'
    ),
    array('XML_PATH_USE_USER_AGENT', 'Magento\Core\Model\Session\AbstractSession',
        'Magento_Core_Model_Session_Validator::XML_PATH_USE_USER_AGENT'
    ),

    array('XML_NODE_DIRECT_FRONT_NAMES', 'Magento\App\Request\Http'),

    array('XML_NODE_USET_AGENT_SKIP', 'Magento\Core\Model\Session\AbstractSession'),
    array('XML_PAGE_TYPE_RENDER_INHERITED', 'Magento\Core\Controller\Varien\Action'),
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
    array('CONFIG_KEY_PATH_TO_MAP_FILE', 'Magento\Core\Model\Resource\Setup\Migration'),
    array('XML_PATH_IGNORE_DEV_MODE', 'Magento\App\UpdaterInterface'),
    array('XML_PATH_SKIP_PROCESS_MODULES_UPDATES', 'Magento\App\UpdaterInterface'),
    array('XML_NODE_SESSION_SAVE', 'Magento\Core\Model\Session\AbstractSession',
        'Magento_Core_Model_Session_AbstractSession::PARAM_SESSION_SAVE_METHOD'
    ),
    array('XML_NODE_SESSION_SAVE_PATH', 'Magento\Core\Model\Session\AbstractSession',
        'Magento_Core_Model_Session_AbstractSession::PARAM_SESSION_SAVE_PATH'
    ),
    array('XML_PATH_USE_CUSTOM_ADMIN_PATH', 'Magento\Backend\Helper\Data'),
    array('XML_PATH_CUSTOM_ADMIN_PATH', 'Magento\Backend\Helper\Data'),
    array('XML_PATH_BACKEND_AREA_FRONTNAME', 'Magento\Backend\Helper\Data'),
    array('PARAM_BACKEND_FRONT_NAME', 'Magento\Backend\Helper\Data'),

);
