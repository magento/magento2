<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Catalog\Model\Product;
use Magento\Downloadable\Helper\Data as DownloadableHelper;
use Magento\Framework\Data\Collection;
use Magento\Framework\GraphQl\Query\EnumLookup;
use Magento\Downloadable\Model\LinkFactory;
use Magento\Downloadable\Model\SampleFactory;

/**
 * @inheritdoc
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
     * @var LinkFactory
     */
    private $linkFactory;

    /**
     * @var SampleFactory
     */
    private $sampleFactory;

    /**
     *
     * @param EnumLookup $enumLookup
     * @param DownloadableHelper $downloadableHelper
     * @param LinkFactory $linkFactory
     * @param SampleFactory $sampleFactory
     */
    public function __construct(
        EnumLookup $enumLookup,
        DownloadableHelper $downloadableHelper,
        LinkFactory $linkFactory,
        SampleFactory $sampleFactory
    ) {
        $this->enumLookup = $enumLookup;
        $this->downloadableHelper = $downloadableHelper;
        $this->linkFactory = $linkFactory;
        $this->sampleFactory = $sampleFactory;
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
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var QuoteItem $quoteItem */
        $quoteItem = $value['model'];

        /** @var Product $product */
        $product = $quoteItem->getProduct();

        $data = null;
        if (in_array($product->getTypeId(), ['downloadable', 'virtual'])) {
            if ($field->getName() === 'downloadable_product_links') {
                $links = $this->linkFactory->create()->getResourceCollection();
                $links->addTitleToResult($product->getStoreId())
                    ->addPriceToResult($product->getStore()->getWebsiteId())
                    ->addProductToFilter($product->getId());

                if ($product->getLinksPurchasedSeparately() == true) {
                    $selectedLinksIds = explode(',', $quoteItem->getOptionByCode('downloadable_link_ids')->getValue());
                    if (count($selectedLinksIds) > 0) {
                        $links->addFieldToFilter('main_table.link_id', ['in' => $selectedLinksIds]);
                    }
                }

                $data = $this->formatLinks(
                    $links
                );
            } elseif ($field->getName() === 'downloadable_product_samples') {
                $samples = $this->sampleFactory->create()->getResourceCollection();
                $samples->addTitleToResult($product->getStoreId())
                    ->addProductToFilter($product->getId());

                $data = $this->formatSamples(
                    $samples
                );
            }
        }

        return $data;
    }

    /**
     * Format links from collection as array
     *
     * @param Collection $links
     * @return array
     */
    private function formatLinks(Collection $links) : array
    {
        $resultData = [];
        foreach ($links as $linkKey => $link) {
            /** @var \Magento\Downloadable\Model\Link $link */
            $resultData[$linkKey]['id'] = $link->getId();
            $resultData[$linkKey]['title'] = $link->getTitle();
            $resultData[$linkKey]['sort_order'] = $link->getSortOrder();
            $resultData[$linkKey]['is_shareable'] = $this->downloadableHelper->getIsShareable($link);
            $resultData[$linkKey]['price'] = $link->getPrice();
            $resultData[$linkKey]['number_of_downloads'] = $link->getNumberOfDownloads();
            $sampleType = $link->getSampleType();
            $linkType = $link->getLinkType();

            if ($linkType !== null) {
                $resultData[$linkKey]['link_type']
                    = $this->enumLookup->getEnumValueFromField('DownloadableFileTypeEnum', $linkType);
            }

            if ($sampleType !== null) {
                $resultData[$linkKey]['sample_type']
                    = $this->enumLookup->getEnumValueFromField('DownloadableFileTypeEnum', $sampleType);
            }

            $resultData[$linkKey]['sample_file'] = $link->getSampleFile();
            $resultData[$linkKey]['sample_url'] = $link->getSampleUrl();
        }
        return $resultData;
    }

    /**
     * Format links from collection as array
     *
     * @param Collection $samples
     * @return array
     */
    private function formatSamples(Collection $samples) : array
    {
        $resultData = [];
        foreach ($samples as $sampleKey => $sample) {
            /** @var \Magento\Downloadable\Model\Sample $sample */
            $resultData[$sampleKey]['id'] = $sample->getId();
            $resultData[$sampleKey]['title'] = $sample->getTitle();
            $resultData[$sampleKey]['sort_order'] = $sample->getSortOrder();
            $resultData[$sampleKey]['sample_type']
                = $this->enumLookup->getEnumValueFromField('DownloadableFileTypeEnum', $sample->getSampleType());
            $resultData[$sampleKey]['sample_file'] = $sample->getSampleFile();
            $resultData[$sampleKey]['sample_url'] = $sample->getSampleUrl();
        }
        return $resultData;
    }
}
