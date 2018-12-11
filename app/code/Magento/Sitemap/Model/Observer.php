<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Model;

use Magento\Store\Model\App\Emulation;
use Magento\Sitemap\Model\SitemapSendEmail as SitemapEmail;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use ResourceModel\Sitemap\CollectionFactory;
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
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Sitemap\Model\ResourceModel\Sitemap\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Emulation
     */
    private $appEmulation;

    /**
     * @var $sitemapEmail
     */
    private $sitemapEmail;

    /**
     * Observer constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param CollectionFactory $collectionFactory
     * @param StoreManagerInterface $storeManager
     * @param SitemapSendEmail $sitemapEmail
     * @param Emulation $appEmulation
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager,
        SitemapEmail $sitemapEmail,
        Emulation $appEmulation
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_collectionFactory = $collectionFactory;
        $this->_storeManager = $storeManager;
        $this->appEmulation = $appEmulation;
        $this->sitemapEmail = $sitemapEmail;
    }

    /**
     * Generate sitemaps
     *
     * @return void
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function scheduledGenerateSitemaps()
    {
        $errors = [];

        // check if scheduled generation enabled
        if (!$this->_scopeConfig->isSetFlag(
            self::XML_PATH_GENERATION_ENABLED,
            ScopeInterface::SCOPE_STORE
        )
        ) {
            return;
        }

        $collection = $this->_collectionFactory->create();
        /* @var $collection \Magento\Sitemap\Model\ResourceModel\Sitemap\Collection */
        foreach ($collection as $sitemap) {
            /* @var $sitemap \Magento\Sitemap\Model\Sitemap */
            try {
                $this->appEmulation->startEnvironmentEmulation(
                    $sitemap->getStoreId(),
                    \Magento\Framework\App\Area::AREA_FRONTEND,
                    true
                );

                $sitemap->generateXml();
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();

                $this->sitemapEmail->sendErrorEmail($errors);
            } finally {
                $this->appEmulation->stopEnvironmentEmulation();
            }
        }
    }
}
