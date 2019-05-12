<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogUrlRewrite\Model\Products\AdaptUrlRewritesToVisibilityAttribute;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;

/**
 * Consider URL rewrites on change product visibility via mass action
 */
class ProcessUrlRewriteOnChangeProductVisibilityObserver implements ObserverInterface
{
    /**
     * @var AdaptUrlRewritesToVisibilityAttribute
     */
    private $adaptUrlRewritesToVisibility;

    /**
     * @param AdaptUrlRewritesToVisibilityAttribute $adaptUrlRewritesToVisibility
     */
    public function __construct(AdaptUrlRewritesToVisibilityAttribute $adaptUrlRewritesToVisibility)
    {
        $this->adaptUrlRewritesToVisibility = $adaptUrlRewritesToVisibility;
    }

    /**
     * Generate urls for UrlRewrites and save it in storage
     *
     * @param Observer $observer
     * @return void
     * @throws UrlAlreadyExistsException
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        $attrData = $event->getAttributesData();
        $productIds = $event->getProductIds();
        $visibility = $attrData[ProductInterface::VISIBILITY] ?? 0;

        if (!$visibility || !$productIds) {
            return;
        }

        $this->adaptUrlRewritesToVisibility->execute($productIds, (int)$visibility);
    }
}
