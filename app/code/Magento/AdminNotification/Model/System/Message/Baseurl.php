<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdminNotification\Model\System\Message;

use Magento\Store\Model\Store;

class Baseurl implements \Magento\Framework\Notification\MessageInterface
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_config;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\Config\ValueFactory
     */
    protected $_configValueFactory;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\App\Config\ValueFactory $configValueFactory
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\Config\ValueFactory $configValueFactory
    ) {
        $this->_urlBuilder = $urlBuilder;
        $this->_config = $config;
        $this->_storeManager = $storeManager;
        $this->_configValueFactory = $configValueFactory;
    }

    /**
     * Get url for config settings where base url option can be changed
     *
     * @return string
     */
    protected function _getConfigUrl()
    {
        $output = '';
        $defaultUnsecure = $this->_config->getValue(Store::XML_PATH_UNSECURE_BASE_URL, 'default');

        $defaultSecure = $this->_config->getValue(Store::XML_PATH_SECURE_BASE_URL, 'default');

        if ($defaultSecure == \Magento\Store\Model\Store::BASE_URL_PLACEHOLDER ||
            $defaultUnsecure == \Magento\Store\Model\Store::BASE_URL_PLACEHOLDER
        ) {
            $output = $this->_urlBuilder->getUrl('adminhtml/system_config/edit', ['section' => 'web']);
        } else {
            /** @var $dataCollection \Magento\Config\Model\ResourceModel\Config\Data\Collection */
            $dataCollection = $this->_configValueFactory->create()->getCollection();
            $dataCollection->addValueFilter(\Magento\Store\Model\Store::BASE_URL_PLACEHOLDER);

            /** @var $data \Magento\Framework\App\Config\ValueInterface */
            foreach ($dataCollection as $data) {
                if ($data->getScope() == 'stores') {
                    $code = $this->_storeManager->getStore($data->getScopeId())->getCode();
                    $output = $this->_urlBuilder->getUrl(
                        'adminhtml/system_config/edit',
                        ['section' => 'web', 'store' => $code]
                    );
                    break;
                } elseif ($data->getScope() == 'websites') {
                    $code = $this->_storeManager->getWebsite($data->getScopeId())->getCode();
                    $output = $this->_urlBuilder->getUrl(
                        'adminhtml/system_config/edit',
                        ['section' => 'web', 'website' => $code]
                    );
                    break;
                }
            }
        }
        return $output;
    }

    /**
     * Retrieve unique message identity
     *
     * @return string
     */
    public function getIdentity()
    {
        return md5('BASE_URL' . $this->_getConfigUrl());
    }

    /**
     * Check whether
     *
     * @return bool
     */
    public function isDisplayed()
    {
        return (bool)$this->_getConfigUrl();
    }

    /**
     * Retrieve message text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getText()
    {
        return __(
            '{{base_url}} is not recommended to use in a production environment to declare the Base Unsecure '
            . 'URL / Base Secure URL. We highly recommend changing this value in your Magento '
            . '<a href="%1">configuration</a>.',
            $this->_getConfigUrl()
        );
    }

    /**
     * Retrieve message severity
     *
     * @return int
     */
    public function getSeverity()
    {
        return self::SEVERITY_CRITICAL;
    }
}
