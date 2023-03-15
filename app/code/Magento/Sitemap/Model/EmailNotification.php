<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Sitemap\Model;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\ScopeInterface;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Sitemap\Model\Observer as Observer;
use Magento\Store\Model\Store as ModelStore;
use Psr\Log\LoggerInterface;

/**
 *  Sends emails for the scheduled generation of the sitemap file
 */
class EmailNotification
{
    /**
     * EmailNotification constructor.
     * @param StateInterface $inlineTranslation
     * @param TransportBuilder $transportBuilder
     * @param ScopeConfigInterface $scopeConfig Core store config
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly StateInterface $inlineTranslation,
        private readonly TransportBuilder $transportBuilder,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly LoggerInterface $logger
    ) {
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
                    'store' => ModelStore::DEFAULT_STORE_ID,
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
        } catch (Exception $e) {
            $this->logger->error('Sitemap sendErrors: '.$e->getMessage());
        } finally {
            $this->inlineTranslation->resume();
        }
    }
}
