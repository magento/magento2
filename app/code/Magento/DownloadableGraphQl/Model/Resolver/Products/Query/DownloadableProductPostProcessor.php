<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\DownloadableGraphQl\Model\Resolver\Products\Query;

use Magento\Downloadable\Model\Product\Type as Downloadable;

/**
 * Retrieves simple product data for child products, and formats configurable data
 */
class DownloadableProductPostProcessor implements \Magento\Framework\GraphQl\Query\PostFetchProcessorInterface
{
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
                    $samples = $product['downloadable_product_samples'];
                    $resultData[$productKey]['downloadable_product_samples'] = [];
                    
                    foreach ($samples as $sampleKey => $sample) {
                        /** @var \Magento\Downloadable\Model\Sample $sample */
                        $resultData[$productKey]['downloadable_product_samples'][$sampleKey]['id']
                            = $sample->getId();
                        $resultData[$productKey]['downloadable_product_samples'][$sampleKey]['title']
                            = $sample->getTitle();
                        $resultData[$productKey]['downloadable_product_samples'][$sampleKey]['sort_order']
                            = $sample->getSortOrder();
                        $resultData[$productKey]['downloadable_product_samples'][$sampleKey]['sample_type']
                            = $sample->getSampleType();
                        $resultData[$productKey]['downloadable_product_samples'][$sampleKey]['sample_file']
                            = $sample->getSampleFile();
                        $resultData[$productKey]['downloadable_product_samples'][$sampleKey]['sample_url']
                            = $sample->getSampleUrl();
                    }

                    $links = $product['downloadable_product_links'];
                    $resultData[$productKey]['downloadable_product_links'] = [];
                    foreach ($links as $linkKey => $link) {
                        /** @var \Magento\Downloadable\Model\Link $link */
                        $resultData[$productKey]['downloadable_product_links'][$linkKey]['id']
                            = $link->getId();
                        $resultData[$productKey]['downloadable_product_links'][$linkKey]['title']
                            = $link->getTitle();
                        $resultData[$productKey]['downloadable_product_links'][$linkKey]['sort_order']
                            = $link->getSortOrder();
                        $resultData[$productKey]['downloadable_product_links'][$linkKey]['is_shareable']
                            = $link->getIsShareable();
                        $resultData[$productKey]['downloadable_product_links'][$linkKey]['price']
                            = $link->getPrice();
                        $resultData[$productKey]['downloadable_product_links'][$linkKey]['number_of_downloads']
                            = $link->getNumberOfDownloads();
                        $resultData[$productKey]['downloadable_product_links'][$linkKey]['link_type']
                            = $link->getLinkType();
                        $resultData[$productKey]['downloadable_product_links'][$linkKey]['sample_type']
                            = $link->getSampleType();
                        $resultData[$productKey]['downloadable_product_links'][$linkKey]['sample_file']
                            = $link->getSampleFile();
                        $resultData[$productKey]['downloadable_product_links'][$linkKey]['sample_url']
                            = $link->getSampleUrl();
                    }
                }
            }
        }

        return $resultData;
    }

    /**
     * @param \Magento\Downloadable\Model\Link[] $links
     * @return array
     */
    private function formatLinks(array $links)
    {
        $resultData = [];
        foreach ($links as $linkKey => $link) {
            /** @var \Magento\Downloadable\Model\Link $link */
            $resultData[$linkKey]['id'] = $link->getId();
            $resultData[$linkKey]['title'] = $link->getTitle();
            $resultData[$linkKey]['sort_order'] = $link->getSortOrder();
            $resultData[$linkKey]['is_shareable'] = $link->getIsShareable();
            $resultData[$linkKey]['price'] = $link->getPrice();
            $resultData[$linkKey]['number_of_downloads'] = $link->getNumberOfDownloads();
            $resultData[$linkKey]['link_type'] = $link->getLinkType();
            $resultData[$linkKey]['sample_type'] = $link->getSampleType();
            $resultData[$linkKey]['sample_file'] = $link->getSampleFile();
            $resultData[$linkKey]['sample_url'] = $link->getSampleUrl();
        }
        return $resultData;
    }
}
