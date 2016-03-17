<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Config backend model for "Custom Admin URL" option
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Config\Model\Config\Backend\Admin;

class Custom extends \Magento\Framework\App\Config\Value
{
    const CONFIG_SCOPE = 'stores';

    const CONFIG_SCOPE_ID = 0;

    const XML_PATH_UNSECURE_BASE_URL = 'web/unsecure/base_url';
    const XML_PATH_SECURE_BASE_URL = 'web/secure/base_url';
    const XML_PATH_UNSECURE_BASE_LINK_URL = 'web/unsecure/base_link_url';
    const XML_PATH_SECURE_BASE_LINK_URL = 'web/secure/base_link_url';
    const XML_PATH_CURRENCY_OPTIONS_BASE = 'currency/options/base';
    const XML_PATH_ADMIN_SECURITY_USEFORMKEY = 'admin/security/use_form_key';
    const XML_PATH_MAINTENANCE_MODE = 'maintenance_mode';
    const XML_PATH_WEB_COOKIE_COOKIE_LIFETIME = 'web/cookie/cookie_lifetime';
    const XML_PATH_WEB_COOKIE_COOKE_PATH = 'web/cookie/cookie_path';
    const XML_PATH_WEB_COOKIE_COOKIE_DOMAIN = 'web/cookie/cookie_domain';
    const XML_PATH_WEB_COOKIE_HTTPONLY = 'web/cookie/cookie_httponly';
    const XML_PATH_WEB_COOKIE_RESTRICTION = 'web/cookie/cookie_restriction';
    const XML_PATH_GENERAL_LOCALE_TIMEZONE = 'general/locale/timezone';
    const XML_PATH_GENERAL_LOCALE_CODE = 'general/locale/code';
    const XML_PATH_GENERAL_COUNTRY_DEFAULT = 'general/country/default';
    const XML_PATH_SYSTEM_BACKUP_ENABLED = 'system/backup/enabled';
    const XML_PATH_DEV_JS_MERGE_FILES = 'dev/js/merge_files';
    const XML_PATH_DEV_JS_MINIFY_FILES = 'dev/js/minify_files';
    const XML_PATH_DEV_CSS_MERGE_CSS_FILES = 'dev/css/merge_css_files';
    const XML_PATH_DEV_CSS_MINIFY_FILES = 'dev/css/minify_files';
    const XML_PATH_DEV_IMAGE_DEFAULT_ADAPTER = 'dev/image/default_adapter';
    const XML_PATH_WEB_SESSION_USE_FRONTEND_SID = 'web/session/use_frontend_sid';
    const XML_PATH_WEB_SESSION_USE_HTTP_X_FORWARDED_FOR = 'web/session/use_http_x_forwarded_for';
    const XML_PATH_WEB_SESSION_USE_HTTP_VIA = 'web/session/use_http_via';
    const XML_PATH_WEB_SESSION_USE_REMOTE_ADDR = 'web/session/use_remote_addr';
    const XML_PATH_WEB_SESSION_USE_HTTP_USER_AGENT = 'web/session/use_http_user_agent';
    const XML_PATH_CATALOG_FRONTEND_FLAT_CATALOG_CATEGORY = 'catalog/frontend/flat_catalog_category';
    const XML_PATH_CATALOG_FRONTEND_FLAT_CATALOG_PRODUCT = 'catalog/frontend/flat_catalog_product';
    const XML_PATH_TAX_WEEE_ENABLE = 'tax/weee/enable';
    const XML_PATH_CATALOG_SEARCH_ENGINE = 'catalog/search/engine';
    const XML_PATH_CARRIERS = 'carriers';
    const XML_PATH_PAYMENT = 'payment';

    /* @var \Magento\Framework\App\Config\Storage\WriterInterface */
    protected $_configWriter;


    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_configWriter = $configWriter;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Validate value before save
     *
     * @return $this
     */
    public function beforeSave()
    {
        $value = $this->getValue();

        if (!empty($value) && substr($value, -2) !== '}}') {
            $value = rtrim($value, '/') . '/';
        }

        $this->setValue($value);
        return $this;
    }

    /**
     * Change secure/unsecure base_url after use_custom_url was modified
     *
     * @return $this
     */
    public function afterSave()
    {
        $useCustomUrl = $this->getData('groups/url/fields/use_custom/value');
        $value = $this->getValue();

        if ($useCustomUrl == 1 && empty($value)) {
            return $this;
        }

        if ($useCustomUrl == 1) {
            $this->_configWriter->save(
                self::XML_PATH_SECURE_BASE_URL,
                $value,
                self::CONFIG_SCOPE,
                self::CONFIG_SCOPE_ID
            );
            $this->_configWriter->save(
                self::XML_PATH_UNSECURE_BASE_URL,
                $value,
                self::CONFIG_SCOPE,
                self::CONFIG_SCOPE_ID
            );
        }

        return parent::afterSave();
    }
}
