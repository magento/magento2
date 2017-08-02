<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rss\Controller\Adminhtml;

/**
 * Class Feed
 * @package Magento\Rss\Controller
 * @since 2.0.0
 */
abstract class Feed extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Rss\Model\RssManager
     * @since 2.0.0
     */
    protected $rssManager;

    /**
     * @var \Magento\Rss\Model\RssFactory
     * @since 2.0.0
     */
    protected $rssFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Rss\Model\RssManager $rssManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Rss\Model\RssFactory $rssFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Rss\Model\RssManager $rssManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Rss\Model\RssFactory $rssFactory
    ) {
        parent::__construct($context);
        $this->_objectManager->get(\Magento\Backend\Model\UrlInterface::class)->turnOffSecretKey();
        $this->rssManager = $rssManager;
        $this->scopeConfig = $scopeConfig;
        $this->rssFactory = $rssFactory;
    }
}
