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
                }
            }
        }

        return $resultData;
    }
}
