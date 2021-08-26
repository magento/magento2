<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Helper\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;

/**
 * Ads URL to name for Dataprovider form modifiers
 */
class AddUrlToName
{
    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * AddUrlToName constructor.
     *
     * @param LocatorInterface $locator
     * @param UrlInterface $urlBuilder
     * @param Escaper $escaper
     */
    public function __construct(
        LocatorInterface $locator,
        UrlInterface $urlBuilder,
        Escaper $escaper
    ) {
        $this->locator = $locator;
        $this->urlBuilder = $urlBuilder;
        $this->escaper = $escaper;
    }

    /**
     * Add url to product name
     *
     * @param ProductInterface $linkedProduct
     * @return string
     */
    public function addUrlToName(ProductInterface $linkedProduct): string
    {
        $storeId = $this->locator->getStore()->getId();

        $url = $this->urlBuilder->getUrl(
            'catalog/product/edit',
            [
                'id' => $linkedProduct->getId(),
                'store' => $storeId
            ]
        );

        return '<a href="javascript:;" onclick="window.open(\'' . $url . '\', \'_blank\');">'
            . $this->escaper->escapeHtml(
                $linkedProduct->getName()
            ) . '</a>';
    }
}
