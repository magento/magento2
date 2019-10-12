<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Review\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;
use Magento\Review\Block\Product\ReviewRenderer;
use Magento\Store\Model\ScopeInterface;

/**
 * Class PredispatchReviewObserver
 */
class PredispatchReviewObserver implements ObserverInterface
{
    /**
     * Configuration path to review active setting
     */
    const XML_PATH_REVIEW_ACTIVE = 'catalog/review/active';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * PredispatchReviewObserver constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param UrlInterface $url
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        UrlInterface $url
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->url = $url;
    }
    /**
     * Redirect review routes to 404 when review module is disabled.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if (!$this->scopeConfig->getValue(
            self::XML_PATH_REVIEW_ACTIVE,
            ScopeInterface::SCOPE_STORE
        )
        ) {
            $defaultNoRouteUrl = $this->scopeConfig->getValue(
                'web/default/no_route',
                ScopeInterface::SCOPE_STORE
            );
            $redirectUrl = $this->url->getUrl($defaultNoRouteUrl);
            $observer->getControllerAction()
                ->getResponse()
                ->setRedirect($redirectUrl);
        }
    }
}
