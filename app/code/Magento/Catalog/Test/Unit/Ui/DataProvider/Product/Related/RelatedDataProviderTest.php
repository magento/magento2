<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Related;

use Magento\Catalog\Ui\DataProvider\Product\Related\RelatedDataProvider;

/**
 * Class RelatedDataProviderTest
 */
class RelatedDataProviderTest extends AbstractDataProviderTest
{
    /**
     * {@inheritdoc}
     */
    protected function getModel()
    {
        return $this->objectManager->getObject(RelatedDataProvider::class, [
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
