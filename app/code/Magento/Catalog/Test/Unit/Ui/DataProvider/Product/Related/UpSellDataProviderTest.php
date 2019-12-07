<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Related;

use Magento\Catalog\Ui\DataProvider\Product\Related\UpSellDataProvider;

/**
 * Class UpSellDataProviderTest
 */
class UpSellDataProviderTest extends AbstractDataProviderTest
{
    /**
     * {@inheritdoc}
     */
    protected function getModel()
    {
        return $this->objectManager->getObject(UpSellDataProvider::class, [
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
