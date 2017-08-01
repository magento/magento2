<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Observer;

use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\CatalogUrlRewrite\Observer\ProductProcessUrlRewriteRemovingObserver
 *
 * @since 2.0.0
 */
class ProductProcessUrlRewriteRemovingObserver implements ObserverInterface
{
    /**
     * @var UrlPersistInterface
     * @since 2.0.0
     */
    protected $urlPersist;

    /**
     * @param UrlPersistInterface $urlPersist
     * @since 2.0.0
     */
    public function __construct(
        UrlPersistInterface $urlPersist
    ) {
        $this->urlPersist = $urlPersist;
    }

    /**
     * Remove product urls from storage
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $observer->getEvent()->getProduct();

        if ($product->getId()) {
            $this->urlPersist->deleteByData(
                [
                    UrlRewrite::ENTITY_ID => $product->getId(),
                    UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
                ]
            );
        }
    }
}
