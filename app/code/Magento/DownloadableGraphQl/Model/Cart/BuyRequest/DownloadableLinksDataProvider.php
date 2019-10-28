<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DownloadableGraphQl\Model\Cart\BuyRequest;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\QuoteGraphQl\Model\Cart\BuyRequest\BuyRequestDataProviderInterface;

/**
 * DataProvider for building downloadable product links in buy requests
 */
class DownloadableLinksDataProvider implements BuyRequestDataProviderInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $cartItemData): array
    {
        $linksData = [];

        if (isset($cartItemData['data']) && isset($cartItemData['data']['sku'])) {
            $sku = $cartItemData['data']['sku'];
            $product = $this->productRepository->get($sku);

            if ($product->getLinksPurchasedSeparately() && isset($cartItemData['downloadable_product_links'])) {
                $downloadableLinks = $cartItemData['downloadable_product_links'];
                $linksData = array_unique(array_column($downloadableLinks, 'link_id'));
            }
        }

        return (count($linksData) > 0 ? ['links' => $linksData] : []);
    }
}
