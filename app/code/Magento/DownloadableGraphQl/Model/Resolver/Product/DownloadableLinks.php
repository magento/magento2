<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DownloadableGraphQl\Model\Resolver\Product;

use Magento\Catalog\Model\Product;
use Magento\Downloadable\Helper\Data as DownloadableHelper;
use Magento\Downloadable\Model\Link;
use Magento\DownloadableGraphQl\Model\ResourceModel\GetDownloadableProductLinks;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\EnumLookup;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\UrlInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;

/**
 * Resolver fetches downloadable product links and formats it according to the GraphQL schema.
 */
class DownloadableLinks implements ResolverInterface
{
    /**
     * @var EnumLookup
     */
    private $enumLookup;

    /**
     * @var DownloadableHelper
     */
    private $downloadableHelper;

    /**
     * @var GetDownloadableProductLinks
     */
    private $downloadableProductLinks;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * DownloadableLinks constructor.
     *
     * @param DownloadableHelper $downloadableHelper
     * @param EnumLookup $enumLookup
     * @param GetDownloadableProductLinks $getDownloadableProductLinks
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        DownloadableHelper $downloadableHelper,
        EnumLookup $enumLookup,
        GetDownloadableProductLinks $getDownloadableProductLinks,
        UrlInterface $urlBuilder
    ) {
        $this->enumLookup = $enumLookup;
        $this->downloadableHelper = $downloadableHelper;
        $this->downloadableProductLinks = $getDownloadableProductLinks;
        $this->urlBuilder = $urlBuilder;
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

        $data = $this->formatLinks($links);

        return $data;
    }

    /**
     * Format links from collection as array
     *
     * @param array $links
     * @return array
     */
    private function formatLinks(array $links = []): array
    {
        $resultData = [];

        try {
            foreach ($links as $linkKey => $link) {
                /** @var Link $link */
                $resultData[$linkKey]['id'] = $link->getId();
                $resultData[$linkKey]['title'] = $link->getTitle();
                $resultData[$linkKey]['sort_order'] = $link->getSortOrder();
                $resultData[$linkKey]['is_shareable'] = $this->downloadableHelper->getIsShareable($link);
                $resultData[$linkKey]['price'] = $link->getPrice();
                $resultData[$linkKey]['number_of_downloads'] = $link->getNumberOfDownloads();
                $sampleType = $link->getSampleType();
                $linkType = $link->getLinkType();

                if ($linkType !== null) {
                    $resultData[$linkKey]['link_type'] = $this->enumLookup->getEnumValueFromField(
                        'DownloadableFileTypeEnum',
                        $linkType
                    );
                }

                if ($sampleType !== null) {
                    $resultData[$linkKey]['sample_type'] = $this->enumLookup->getEnumValueFromField(
                        'DownloadableFileTypeEnum',
                        $sampleType
                    );
                }

                $resultData[$linkKey]['sample_file'] = $link->getSampleFile();
                $resultData[$linkKey]['sample_url'] = $this->urlBuilder->getUrl(
                    'downloadable/download/linkSample',
                    ['link_id' => $link->getId()]
                );
            }
        } catch (\Exception $e) {
            // Do nothing
        }

        return $resultData;
    }
}
