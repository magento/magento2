<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerDownloadableGraphQl\Model\Resolver;

use Magento\DownloadableGraphQl\Model\ResourceModel\GetPurchasedDownloadableProducts;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\UrlInterface;
use Magento\GraphQl\Model\Query\ContextInterface;

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
        ?array $value = null,
        ?array $args = null
    ) {
        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        $purchasedProducts = $this->getPurchasedDownloadableProducts->execute($context->getUserId());
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
