<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Ui\DataProvider;

class Attributes extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    protected $collection;

    /**
     * @var \Magento\ConfigurableProduct\Model\ConfigurableAttributeHandler
     */
    private $configurableAttributeHandler;

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
        $this->configurableAttributeHandler = $configurableAttributeHandler;
        $this->collection = $configurableAttributeHandler->getApplicableAttributes();
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @inheritDoc
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }

        return $this->getCollection()->toArray();
    }
}
