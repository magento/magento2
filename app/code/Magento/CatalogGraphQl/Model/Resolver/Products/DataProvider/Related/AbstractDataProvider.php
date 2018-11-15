<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Related;

abstract class AbstractDataProvider
{


    /**
     * @var array
     */
    protected $fields = [];
    /**
     * @var $collection
     */
    protected $collection;


    /**
     * @param $field
     */
    public function addFieldToSelect($field)
    {
        $this->fields = $field;
    }


    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return mixed
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @param $product
     * @return mixed
     */
    public function getData($product)
    {
        $this->prepareCollection($product);
        return $this->collection;

    }

    /**
     * @param $product
     */
    protected function prepareCollection($product): void
    {
        $this->collection = $product->getRelatedProducts();
        $this->collection->addAttributeToSelect($this->getFields());
    }


}