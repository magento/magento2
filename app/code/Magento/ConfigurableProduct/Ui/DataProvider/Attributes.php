<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Ui\DataProvider;

use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class Attributes extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    protected $collection;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Magento\ConfigurableProduct\Model\ConfigurableAttributeHandler $configurableAttributeHandler
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\ConfigurableProduct\Model\ConfigurableAttributeHandler $configurableAttributeHandler,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $configurableAttributeHandler->getApplicableAttributes();
    }

    /**
     * Getting the product attribute collection.
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        $items = [];
        $this->getCollection()->getSelect()->where(
            '(`apply_to` IS NULL) OR
            (
                FIND_IN_SET(' .
            sprintf("'%s'", Type::TYPE_SIMPLE) . ',
                    `apply_to`
                ) AND
                FIND_IN_SET(' .
            sprintf("'%s'", Type::TYPE_VIRTUAL) . ',
                    `apply_to`
                ) AND
                FIND_IN_SET(' .
            sprintf("'%s'", Configurable::TYPE_CODE) . ',
                    `apply_to`
                )
             )'
        );
        foreach ($this->getCollection()->getItems() as $attribute) {
            $items[] = $attribute->toArray();
        }
        return [
            'totalRecords' => $this->collection->getSize(),
            'items' => $items
        ];
    }
}
