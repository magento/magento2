<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Log Cron Model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Log\Model;

class Cron extends \Magento\Framework\Model\AbstractModel
{
    const XML_PATH_EMAIL_LOG_CLEAN_TEMPLATE = 'system/log/error_email_template';

    const XML_PATH_EMAIL_LOG_CLEAN_IDENTITY = 'system/log/error_email_identity';

    const XML_PATH_EMAIL_LOG_CLEAN_RECIPIENT = 'system/log/error_email';

    const XML_PATH_LOG_CLEAN_ENABLED = 'system/log/enabled';

    /**
     * Error messages
     *
     * @var array
     */
    protected $_errors = [];

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Log\Model\Log
     */
    protected $_log;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Log\Model\Log $log
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Log\Model\Log $log,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        $this->_transportBuilder = $transportBuilder;
        $this->_log = $log;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        $this->inlineTranslation = $inlineTranslation;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Send Log Clean Warnings
     *
     * @return $this
     */
    protected function _sendLogCleanEmail()
    {
        if (!$this->_errors) {
            return $this;
        }
        if (!$this->_scopeConfig->getValue(
            self::XML_PATH_EMAIL_LOG_CLEAN_RECIPIENT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )
        ) {
            return $this;
        }

        $this->inlineTranslation->suspend();
        $transport = $this->_transportBuilder->setTemplateIdentifier(
            $this->_scopeConfig->getValue(
                self::XML_PATH_EMAIL_LOG_CLEAN_TEMPLATE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        )->setTemplateOptions(
            [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->_storeManager->getStore()->getId(),
            ]
        )->setTemplateVars(
            ['warnings' => join("\n", $this->_errors)]
        )->setFrom(
            $this->_scopeConfig->getValue(
                self::XML_PATH_EMAIL_LOG_CLEAN_IDENTITY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        )->addTo(
            $this->_scopeConfig->getValue(
                self::XML_PATH_EMAIL_LOG_CLEAN_RECIPIENT,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        )->getTransport();

        $transport->sendMessage();

        $this->inlineTranslation->resume();

        return $this;
    }

    /**
     * Clean logs
     *
     * @return $this
     */
    public function logClean()
    {
        if (!$this->_scopeConfig->isSetFlag(
            self::XML_PATH_LOG_CLEAN_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )
        ) {
            return $this;
        }

        $this->_errors = [];

        try {
            $this->_log->clean();
        } catch (\Exception $e) {
            $this->_errors[] = $e->getMessage();
            $this->_errors[] = $e->getTrace();
        }

        $this->_sendLogCleanEmail();

        return $this;
    }
}
