<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DownloadableGraphQl\Model\Resolver\Product;

use Magento\Catalog\Model\Product;
use Magento\DownloadableGraphQl\Service\FormatProductLinksService;
use Magento\DownloadableGraphQl\Model\ResourceModel\GetDownloadableProductLinks;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote\Item as QuoteItem;

/**
 * Resolver fetches downloadable product links and formats it according to the GraphQL schema.
 */
class DownloadableLinks implements ResolverInterface
{
    /**
     * @var FormatProductLinksService
     */
    private $formatLinksService;

    /**
     * @var GetDownloadableProductLinks
     */
    private $downloadableProductLinks;

    /**
     * DownloadableLinks constructor.
     *
     * @param FormatProductLinksService $formatProductLinksService
     * @param GetDownloadableProductLinks $getDownloadableProductLinks
     */
    public function __construct(
        FormatProductLinksService $formatProductLinksService,
        GetDownloadableProductLinks $getDownloadableProductLinks
    ) {
        $this->formatLinksService = $formatProductLinksService;
        $this->downloadableProductLinks = $getDownloadableProductLinks;
    }

    /**
     * Fetches downloadable product links and formats it according to the GraphQL schema.
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|Value|mixed|null
     * @throws GraphQlInputException
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var QuoteItem $quoteItem */
        $quoteItem = $value['model'];

        /** @var Product $product */
        $product = $quoteItem->getProduct();

        if (!in_array($product->getTypeId(), ['downloadable', 'virtual'])) {
            throw new GraphQlInputException(
                __('Wrong product type. Links are available for Downloadable and Virtual product types')
            );
        }

        $links = $this->downloadableProductLinks->execute(
            $product,
            explode(',', $quoteItem->getOptionByCode('downloadable_link_ids')->getValue())
        );

        $data = $this->formatLinksService->execute($links);

        return $data;
    }
}
