<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\DownloadableGraphQl\Model\Resolver\Products\Query;

use Magento\Downloadable\Model\Product\Type as Downloadable;
use Magento\Framework\Data\Collection;
use Magento\Framework\GraphQl\Query\EnumLookup;
use Magento\Downloadable\Helper\Data as DownloadableHelper;

/**
 * Retrieves simple product data for child products, and formats configurable data
 */
class DownloadableProductPostProcessor implements \Magento\Framework\GraphQl\Query\PostFetchProcessorInterface
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
     * @param EnumLookup $enumLookup
     */
    public function __construct(EnumLookup $enumLookup, DownloadableHelper $downloadableHelper)
    {
        $this->enumLookup = $enumLookup;
        $this->downloadableHelper = $downloadableHelper;
    }

    /**
     * Process all downloadable product data, including adding simple product data and formatting relevant attributes.
     *
     * @param array $resultData
     * @return array
     */
    public function process(array $resultData)
    {
        foreach ($resultData as $productKey => $product) {
            if ($product['type_id'] === Downloadable::TYPE_DOWNLOADABLE) {
                if (isset($product['downloadable_product_samples'])) {
                    $resultData[$productKey]['downloadable_product_samples']
                        = $this->formatSamples($product['downloadable_product_samples']);
                }
                if (isset($product['downloadable_product_links'])) {
                    $resultData[$productKey]['downloadable_product_links']
                        = $this->formatLinks($product['downloadable_product_links']);
                }
            }
        }

        return $resultData;
    }

    /**
     * Format links from collection as array
     *
     * @param Collection $links
     * @return array
     */
    private function formatLinks(Collection $links)
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
