<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DownloadableGraphQl\Model\Resolver;

use Magento\DownloadableGraphQl\Model\ResourceModel\GetPurchasedDownloadableProducts;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\UrlInterface;

/**
 * @inheritdoc
 *
 * Returns available downloadable products for customer
 */
class CustomerDownloadableProducts implements ResolverInterface
{
    /**
     * @var GetPurchasedDownloadableProducts
     */
    private $getPurchasedDownloadableProducts;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param GetPurchasedDownloadableProducts $getPurchasedDownloadableProducts
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        GetPurchasedDownloadableProducts $getPurchasedDownloadableProducts,
        UrlInterface $urlBuilder
    ) {
        $this->getPurchasedDownloadableProducts = $getPurchasedDownloadableProducts;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $currentUserId = $context->getUserId();
        $purchasedProducts = $this->getPurchasedDownloadableProducts->execute($currentUserId);
        $productsData = [];

        /* The fields names are hardcoded since there's no existing name reference in the code */
        foreach ($purchasedProducts as $purchasedProduct) {
            if ($purchasedProduct['number_of_downloads_bought']) {
                $remainingDownloads = $purchasedProduct['number_of_downloads_bought'] -
                    $purchasedProduct['number_of_downloads_used'];
            } else {
                $remainingDownloads = __('Unlimited');
            }

            $productsData[] = [
                'order_increment_id' => $purchasedProduct['order_increment_id'],
                'date' => $purchasedProduct['created_at'],
                'status' => $purchasedProduct['status'],
                'download_url' => $this->urlBuilder->getUrl(
                    'downloadable/download/link',
                    ['id' => $purchasedProduct['link_hash'], '_secure' => true]
                ),
                'remaining_downloads' => $remainingDownloads
            ];
        }

        return ['items' => $productsData];
    }
}
