<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Sitemap\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\ScopeInterface;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Sitemap\Model\Observer as Observer;
use Psr\Log\LoggerInterface;

/**
 *  Sends emails for the scheduled generation of the sitemap file
 */
class EmailNotification
{
    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    private $inlineTranslation;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var \Psr\Log\LoggerInterface $logger
     */
    private $logger;

    /**
     * EmailNotification constructor.
     * @param StateInterface $inlineTranslation
     * @param TransportBuilder $transportBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->logger = $logger;
    }

    /**
     * Send's error email if sitemap generated with errors.
     *
     * @param array| $errors
     */
    public function sendErrors($errors)
    {
        $this->inlineTranslation->suspend();
        try {
            $this->transportBuilder->setTemplateIdentifier(
                $this->scopeConfig->getValue(
                    Observer::XML_PATH_ERROR_TEMPLATE,
                    ScopeInterface::SCOPE_STORE
                )
            )->setTemplateOptions(
                [
                    'area' => FrontNameResolver::AREA_CODE,
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                ]
            )->setTemplateVars(
                ['warnings' => join("\n", $errors)]
            )->setFrom(
                $this->scopeConfig->getValue(
                    Observer::XML_PATH_ERROR_IDENTITY,
                    ScopeInterface::SCOPE_STORE
                )
            )->addTo(
                $this->scopeConfig->getValue(
                    Observer::XML_PATH_ERROR_RECIPIENT,
                    ScopeInterface::SCOPE_STORE
                )
            );

            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->logger->error('Sitemap sendErrors: '.$e->getMessage());
        } finally {
            $this->inlineTranslation->resume();
        }
    }
}
