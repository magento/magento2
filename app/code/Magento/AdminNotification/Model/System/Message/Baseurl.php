<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminNotification\Model\System\Message;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\Phrase;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Baseurl
 *
 * @package Magento\AdminNotification\Model\System\Message
 * @deprecated 100.1.0
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Baseurl implements MessageInterface
{
    /**
     * @var UrlInterface
     */
    protected $_urlBuilder; //phpcs:ignore

    /**
     * @var ScopeConfigInterface
     */
    protected $_config; //phpcs:ignore

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager; //phpcs:ignore

    /**
     * @var ValueFactory
     */
    protected $_configValueFactory; //phpcs:ignore

    /**
     * @param ScopeConfigInterface $config
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlBuilder
     * @param ValueFactory $configValueFactory
     */
    public function __construct(
        ScopeConfigInterface $config,
        StoreManagerInterface $storeManager,
        UrlInterface $urlBuilder,
        ValueFactory $configValueFactory
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
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    protected function _getConfigUrl(): string //phpcs:ignore
    {
        $output = '';
        $defaultUnsecure = $this->_config->getValue(Store::XML_PATH_UNSECURE_BASE_URL, 'default');

        $defaultSecure = $this->_config->getValue(Store::XML_PATH_SECURE_BASE_URL, 'default');

        if ($defaultSecure === Store::BASE_URL_PLACEHOLDER ||
            $defaultUnsecure === Store::BASE_URL_PLACEHOLDER
        ) {
            $output = $this->_urlBuilder->getUrl('adminhtml/system_config/edit', ['section' => 'web']);
        } else {
            /** @var \Magento\Config\Model\ResourceModel\Config\Data\Collection $dataCollection */
            $dataCollection = $this->_configValueFactory->create()->getCollection();
            $dataCollection->addValueFilter(Store::BASE_URL_PLACEHOLDER);

            /** @var \Magento\Framework\App\Config\ValueInterface $data */
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
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getIdentity(): string
    {
        return md5('BASE_URL' . $this->_getConfigUrl());
    }

    /**
     * Check whether
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isDisplayed(): bool
    {
        return (bool)$this->_getConfigUrl();
    }

    /**
     * Retrieve message text
     *
     * @return Phrase
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getText(): Phrase
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
    public function getSeverity(): int
    {
        return self::SEVERITY_CRITICAL;
    }
}
