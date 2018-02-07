<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\DownloadableGraphQl\Model\Resolver\Products\DataProvider\Product\Formatter;

use Magento\Catalog\Model\Product;
use Magento\Downloadable\Model\Product\Type as Downloadable;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\FormatterInterface;
use Magento\Framework\Data\Collection;
use Magento\Framework\GraphQl\Query\EnumLookup;
use Magento\Downloadable\Helper\Data as DownloadableHelper;
use Magento\Downloadable\Model\ResourceModel\Sample\Collection as SampleCollection;
use Magento\Downloadable\Model\ResourceModel\Link\Collection as LinkCollection;

/**
 * Format for downloadable product types
 */
class DownloadableOptions implements FormatterInterface
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
     * @var SampleCollection
     */
    private $sampleCollection;

    /**
     * @var LinkCollection
     */
    private $linkCollection;

    /**
     * @param EnumLookup $enumLookup
     * @param DownloadableHelper $downloadableHelper
     * @param SampleCollection $sampleCollection
     * @param LinkCollection $linkCollection
     */
    public function __construct(
        EnumLookup $enumLookup,
        DownloadableHelper $downloadableHelper,
        SampleCollection $sampleCollection,
        LinkCollection $linkCollection
    ) {
        $this->enumLookup = $enumLookup;
        $this->downloadableHelper = $downloadableHelper;
        $this->sampleCollection = $sampleCollection;
        $this->linkCollection = $linkCollection;
    }

    /**
     * Add downloadable options to configurable types
     *
     * {@inheritdoc}
     */
    public function format(Product $product, array $productData = [])
    {
        if ($product->getTypeId() === Downloadable::TYPE_DOWNLOADABLE) {
            $samples = $this->sampleCollection->addTitleToResult($product->getStoreId())
                ->addProductToFilter($product->getId());
            $links = $this->linkCollection->addTitleToResult($product->getStoreId())
                ->addPriceToResult($product->getStore()->getWebsiteId())
                ->addProductToFilter($product->getId());
            $productData['downloadable_product_links'] =  $this->formatLinks(
                $links
            );
            $productData['downloadable_product_samples'] = $this->formatSamples(
                $samples
            );
        }

        return $productData;
    }

    /**
     * Format links from collection as array
     *
     * @param LinkCollection $links
     * @return array
     */
    private function formatLinks(LinkCollection $links)
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
            $resultData[$linkKey]['link_type']
                = $this->enumLookup->getEnumValueFromField('DownloadableFileTypeEnum', $link->getLinkType());
            $resultData[$linkKey]['sample_type']
                = $this->enumLookup->getEnumValueFromField('DownloadableFileTypeEnum', $link->getSampleType());
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
    private function formatSamples(Collection $samples)
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
