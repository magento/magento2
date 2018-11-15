<?php


namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Related;


class RelatedDataProvider extends AbstractDataProvider
{


    protected function prepareCollection($product): void
    {
        $this->collection = $product->getRelatedProductCollection();
        $this->collection->addAttributeToSelect($this->getFields());
    }


}