<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product\ProductList;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Magento\Catalog\Model\Product\ProductList\CollectionLoader;

class CollectionLoaderTest extends \PHPUnit\Framework\TestCase
{
    public function testGetOrder()
    {
        $collectionLoader = new CollectionLoader();
        $mockedCollection = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockedCollection->expects($this->once())->method('load');

        $collectionLoader->load($mockedCollection);
    }
}
