<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Ui\DataProvider\Product\Listing\Collector;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductRenderExtensionFactory;
use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Catalog\Ui\DataProvider\Product\ProductRenderCollectorInterface;
use Magento\Framework\App\State;
use Magento\Review\Block\Product\ReviewRenderer;

/**
 * Collect enough information for rendering reviews, ratings, etc...
 * Can be used on product page, on product listing page
 * @since 2.2.0
 */
class Review implements ProductRenderCollectorInterface
{
    /** Review html key */
    const KEY = "review_html";

    /**
     * @var ReviewRenderer
     * @since 2.2.0
     */
    private $reviewRenderer;

    /**
     * @var string
     * @since 2.2.0
     */
    private $reviewType;

    /**
     * @var ProductRenderExtensionFactory
     * @since 2.2.0
     */
    private $productRenderExtensionFactory;

    /**
     * @var State
     * @since 2.2.0
     */
    private $appState;

    /**
     * @param ReviewRenderer $reviewRenderer
     * @param ProductRenderExtensionFactory $productRenderExtensionFactory
     * @param State $appState
     * @param string $reviewType
     * @since 2.2.0
     */
    public function __construct(
        ReviewRenderer $reviewRenderer,
        ProductRenderExtensionFactory $productRenderExtensionFactory,
        State $appState,
        $reviewType = 'short'
    ) {
        $this->reviewRenderer = $reviewRenderer;
        $this->reviewType = $reviewType;
        $this->productRenderExtensionFactory = $productRenderExtensionFactory;
        $this->appState = $appState;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function collect(ProductInterface $product, ProductRenderInterface $productRender)
    {
        $extensionAttributes = $productRender->getExtensionAttributes();

        if (!$extensionAttributes) {
            $extensionAttributes = $this->productRenderExtensionFactory->create();
        }
        $summaryHtml = $this->appState->emulateAreaCode(
            'frontend',
            [$this->reviewRenderer, 'getReviewsSummaryHtml'],
            [$product, $this->reviewType, true]
        );
        $extensionAttributes
            ->setReviewHtml($summaryHtml);

        $productRender->setExtensionAttributes($extensionAttributes);
    }
}
