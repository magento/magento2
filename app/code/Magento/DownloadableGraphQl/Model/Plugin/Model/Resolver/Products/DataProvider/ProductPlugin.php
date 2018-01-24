<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\DownloadableGraphQl\Model\Plugin\Model\Resolver\Products\DataProvider;

use Magento\Downloadable\Model\Product\Type as Downloadable;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product;
use Magento\Downloadable\Model\ResourceModel\Sample\Collection as SampleCollection;

class ProductPlugin
{
    /**
     * @var SampleCollection
     */
    private $sampleCollection;


    /**
     * @param SampleCollection $sampleCollection
     */
    public function __construct(SampleCollection $sampleCollection)
    {
        $this->sampleCollection = $sampleCollection;
    }

    /**
     * Intercept GraphQLCatalog getList, and add any necessary downloadable fields
     *
     * @param Product $subject
     * @param SearchResultsInterface $result
     * @return SearchResultsInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(Product $subject, SearchResultsInterface $result)
    {
        foreach ($result->getItems() as $product) {
            if ($product->getTypeId() === Downloadable::TYPE_DOWNLOADABLE) {
                $extensionAttributes = $product->getExtensionAttributes();
                $samples = $this->sampleCollection->addTitleToResult()->addProductToFilter($product->getId());
                $extensionAttributes->setDownloadableProductSamples($samples);
                $product->setExtensionAttributes($extensionAttributes);
            }
        }
        return $result;
    }
}
