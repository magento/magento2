<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sitemap\Model;

use Exception;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sitemap\Model\EmailNotification as SitemapEmail;
use Magento\Sitemap\Model\ResourceModel\Sitemap\Collection as SitemapCollection;
use Magento\Sitemap\Model\ResourceModel\Sitemap\CollectionFactory;
use Magento\Sitemap\Model\Sitemap as ModelSitemap;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\ScopeInterface;

/**
 * Sitemap module observer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Observer
{
    /**
     * Enable/disable configuration
     */
    const XML_PATH_GENERATION_ENABLED = 'sitemap/generate/enabled';

    /**
     * Cronjob expression configuration
     *
     * @deprecated Use \Magento\Cron\Model\Config\Backend\Sitemap::CRON_STRING_PATH instead.
     */
    const XML_PATH_CRON_EXPR = 'crontab/default/jobs/generate_sitemaps/schedule/cron_expr';

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
     * Observer constructor.
     * @param ScopeConfigInterface $scopeConfig Core store config
     * @param CollectionFactory $collectionFactory
     * @param EmailNotification $emailNotification
     * @param Emulation $appEmulation
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly CollectionFactory $collectionFactory,
        private readonly SitemapEmail $emailNotification,
        private readonly Emulation $appEmulation
    ) {
    }

    /**
     * Generate sitemaps
     *
     * @return void
     * @throws Exception
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function scheduledGenerateSitemaps()
    {
        $errors = [];
        $recipient = $this->scopeConfig->getValue(
            Observer::XML_PATH_ERROR_RECIPIENT,
            ScopeInterface::SCOPE_STORE
        );
        // check if scheduled generation enabled
        if (!$this->scopeConfig->isSetFlag(
            self::XML_PATH_GENERATION_ENABLED,
            ScopeInterface::SCOPE_STORE
        )
        ) {
            return;
        }

        $collection = $this->collectionFactory->create();
        /* @var SitemapCollection $collection */
        foreach ($collection as $sitemap) {
            /* @var ModelSitemap $sitemap */
            try {
                $this->appEmulation->startEnvironmentEmulation(
                    $sitemap->getStoreId(),
                    Area::AREA_FRONTEND,
                    true
                );
                $sitemap->generateXml();
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            } finally {
                $this->appEmulation->stopEnvironmentEmulation();
            }
        }
        if ($errors && $recipient) {
            $this->emailNotification->sendErrors($errors);
        }
    }
}
