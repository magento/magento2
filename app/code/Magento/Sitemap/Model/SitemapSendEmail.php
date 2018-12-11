<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\ScopeInterface;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Store\Model\Store;

/**
 *  Sends emails for the scheduled generation of the sitemap file
 */
class SitemapSendEmail
{
    /**
     * Error email template configuration
     */
    const XML_PATH_ERROR_TEMPLATE = 'sitemap/generate/error_email_template';

    /**
     * Error email identity configuration
     */
    const XML_PATH_ERROR_IDENTITY = 'sitemap/generate/error_email_identity';

    /**
     * 'Send error emails to' configuration
     */
    const XML_PATH_ERROR_RECIPIENT = 'sitemap/generate/error_email';

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    private $inlineTranslation;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $_transportBuilder;

    /**
     * SitemapSendEmail constructor.
     * @param StateInterface $inlineTranslation
     * @param TransportBuilder $transportBuilder
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->inlineTranslation = $inlineTranslation;
        $this->_scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
    }

    /**
     * Send's error email if sitemap generated with errors.
     *
     * @param array $errors
     * @throws \Magento\Framework\Exception\MailException
     */
    public function sendErrorEmail($errors)
    {
        $recipient = $this->_scopeConfig->getValue(
            self::XML_PATH_ERROR_RECIPIENT,
            ScopeInterface::SCOPE_STORE
        );
        if ($errors && $recipient) {
            $this->inlineTranslation->suspend();

            $this->_transportBuilder->setTemplateIdentifier(
                $this->_scopeConfig->getValue(
                    self::XML_PATH_ERROR_TEMPLATE,
                    ScopeInterface::SCOPE_STORE
                )
            )->setTemplateOptions(
                [
                    'area' => FrontNameResolver::AREA_CODE,
                    'store' => Store::DEFAULT_STORE_ID,
                ]
            )->setTemplateVars(
                ['warnings' => join("\n", $errors)]
            )->setFrom(
                $this->_scopeConfig->getValue(
                    self::XML_PATH_ERROR_IDENTITY,
                    ScopeInterface::SCOPE_STORE
                )
            )->addTo($recipient);

            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();

            $this->inlineTranslation->resume();
        }
    }
}
