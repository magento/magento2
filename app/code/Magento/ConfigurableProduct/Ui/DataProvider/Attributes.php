<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Ui\DataProvider;

/**
 * Class \Magento\ConfigurableProduct\Ui\DataProvider\Attributes
 *
 * @since 2.0.0
 */
class Attributes extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     * @since 2.0.0
     */
    protected $collection;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Magento\ConfigurableProduct\Model\ConfigurableAttributeHandler $configurableAttributeHandler
     * @param array $meta
     * @param array $data
     * @since 2.0.0
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
        $this->configurableAttributeHandler = $configurableAttributeHandler;
        $this->collection = $configurableAttributeHandler->getApplicableAttributes();
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     * @since 2.0.0
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getData()
    {
        $items = [];
        $skippedItems = 0;
        foreach ($this->getCollection()->getItems() as $attribute) {
            if ($this->configurableAttributeHandler->isAttributeApplicable($attribute)) {
                $items[] = $attribute->toArray();
            } else {
                $skippedItems++;
            }
        }
        return [
            'totalRecords' => $this->collection->getSize() - $skippedItems,
            'items' => $items
        ];
    }
}
