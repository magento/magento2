<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductAlert\Model\Mailing;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

/**
 * Class to send error emails to administrator
 */
class ErrorEmailSender
{
    /**
     * Error email template configuration
     */
    private const XML_PATH_ERROR_TEMPLATE = 'catalog/productalert_cron/error_email_template';

    /**
     * Error email identity configuration
     */
    private const XML_PATH_ERROR_IDENTITY = 'catalog/productalert_cron/error_email_identity';

    /**
     * 'Send error emails to' configuration
     */
    private const XML_PATH_ERROR_RECIPIENT = 'catalog/productalert_cron/error_email';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var StateInterface
     */
    private $inlineTranslation;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param LoggerInterface $logger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        LoggerInterface $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->logger = $logger;
    }

    /**
     * Send error emails to administrator
     *
     * @param array $errors
     * @param int $storeId
     * @return void
     */
    public function execute(array $errors, int $storeId): void
    {
        if (!count($errors)) {
            return;
        }

        $this->logger->error(
            'Product Alerts: ' . count($errors) . ' errors occurred during sending alerts.'
        );

        if (!$this->scopeConfig->getValue(self::XML_PATH_ERROR_TEMPLATE, ScopeInterface::SCOPE_STORE, $storeId)) {
            return;
        }

        $this->inlineTranslation->suspend();
        $transport = $this->transportBuilder->setTemplateIdentifier(
            $this->scopeConfig->getValue(
                self::XML_PATH_ERROR_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            )
        )->setTemplateOptions(
            [
                'area' => FrontNameResolver::AREA_CODE,
                'store' => $storeId,
            ]
        )->setTemplateVars(
            ['warnings' => join("\n", $errors)]
        )->setFromByScope(
            $this->scopeConfig->getValue(
                self::XML_PATH_ERROR_IDENTITY,
                ScopeInterface::SCOPE_STORE,
                $storeId
            ),
            $storeId
        )->addTo(
            $this->scopeConfig->getValue(
                self::XML_PATH_ERROR_RECIPIENT,
                ScopeInterface::SCOPE_STORE,
                $storeId
            )
        )->getTransport();

        $transport->sendMessage();

        $this->inlineTranslation->resume();
    }
}
