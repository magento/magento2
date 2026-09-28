<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sitemap\Model\Batch;

use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sitemap\Model\EmailNotification as SitemapEmail;
use Magento\Sitemap\Model\ResourceModel\Sitemap\CollectionFactory;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

/**
 * Memory-optimized sitemap observer for scheduled generation using batch processing
 */
class Observer
{
    /**
     * Enable/disable configuration
     */
    private const XML_PATH_GENERATION_ENABLED = 'sitemap/generate/enabled';

    /**
     * 'Send error emails to' configuration
     */
    private const XML_PATH_ERROR_RECIPIENT = 'sitemap/generate/error_email';

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $collectionFactory;

    /**
     * @var SitemapFactory
     */
    private SitemapFactory $batchSitemapFactory;

    /**
     * @var SitemapEmail
     */
    private SitemapEmail $emailNotification;

    /**
     * @var Emulation
     */
    private Emulation $appEmulation;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Observer constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param CollectionFactory    $collectionFactory
     * @param SitemapFactory       $batchSitemapFactory
     * @param SitemapEmail         $emailNotification
     * @param Emulation            $appEmulation
     * @param LoggerInterface      $logger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CollectionFactory $collectionFactory,
        SitemapFactory $batchSitemapFactory,
        SitemapEmail $emailNotification,
        Emulation $appEmulation,
        LoggerInterface $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->collectionFactory = $collectionFactory;
        $this->batchSitemapFactory = $batchSitemapFactory;
        $this->emailNotification = $emailNotification;
        $this->appEmulation = $appEmulation;
        $this->logger = $logger;
    }

    /**
     * Generate sitemaps using memory-optimized batch processing
     *
     * @return void
     * @throws \Exception
     */
    public function scheduledGenerateSitemaps(): void
    {
        $errors = [];
        $recipient = $this->scopeConfig->getValue(
            self::XML_PATH_ERROR_RECIPIENT,
            ScopeInterface::SCOPE_STORE
        );

        if (!$this->scopeConfig->isSetFlag(
            self::XML_PATH_GENERATION_ENABLED,
            ScopeInterface::SCOPE_STORE
        )) {
            return;
        }

        $collection = $this->collectionFactory->create();
        $this->logger->info(sprintf('Found %d sitemap(s) to generate using batch processing', $collection->getSize()));

        /** @var \Magento\Sitemap\Model\Sitemap $sitemap */
        foreach ($collection as $sitemapData) {
            try {
                $storeId = $sitemapData->getStoreId();

                $this->appEmulation->startEnvironmentEmulation(
                    $storeId,
                    Area::AREA_FRONTEND,
                    true
                );

                $batchSitemap = $this->batchSitemapFactory->create();
                $batchSitemap->setData($sitemapData->getData());

                $batchSitemap->generateXml();

                $this->logger->info(
                    "Sitemap generated successfully for store {$storeId}: " . $batchSitemap->getSitemapFilename()
                );

            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
                $this->logger->error($e->getMessage(), ['exception' => $e]);
            } finally {
                $this->appEmulation->stopEnvironmentEmulation();
            }
        }

        if ($errors && $recipient) {
            $this->emailNotification->sendErrors($errors);
        }
    }
}
