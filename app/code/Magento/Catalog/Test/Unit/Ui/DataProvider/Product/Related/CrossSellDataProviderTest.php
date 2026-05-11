<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Related;

use Magento\Catalog\Ui\DataProvider\Product\Related\CrossSellDataProvider;

class CrossSellDataProviderTest extends AbstractDataProviderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getModel()
    {
        return $this->objectManager->getObject(CrossSellDataProvider::class, [
            'name' => 'testName',
            'primaryFieldName' => 'testPrimaryFieldName',
            'requestFieldName' => 'testRequestFieldName',
            'collectionFactory' => $this->collectionFactoryMock,
            'request' => $this->requestMock,
            'productRepository' => $this->productRepositoryMock,
            'productLinkRepository' => $this->productLinkRepositoryMock,
            'addFieldStrategies' => [],
            'addFilterStrategies' => [],
            'meta' => [],
            'data' => []
        ]);
    }
}
