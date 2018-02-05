<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\BundleGraphQl\Model\Plugin\Model\Resolver\Products\DataProvider;

use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product;
use Magento\Bundle\Model\Product\OptionList;

/**
 * Fetch bundle product object and set necessary extension attributes for search result
 */
class ProductPlugin
{
    /**
     * @var OptionList
     */
    private $productOptionList;

    /**
     * @param OptionList $productOptionList
     */
    public function __construct(OptionList $productOptionList)
    {
        $this->productOptionList = $productOptionList;
    }

    /**
     * Intercept GraphQLCatalog getList, and add any necessary bundle fields
     *
     * @param Product $subject
     * @param SearchResultsInterface $result
     * @return SearchResultsInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(Product $subject, SearchResultsInterface $result)
    {
        foreach ($result->getItems() as $product) {
            if ($product->getTypeId() === Bundle::TYPE_CODE) {
                $extensionAttributes = $product->getExtensionAttributes();
                $options = $this->productOptionList->getItems($product);
                $extensionAttributes->setBundleProductOptions($options);
                $product->setExtensionAttributes($extensionAttributes);
            }
        }
        return $result;
    }
}
