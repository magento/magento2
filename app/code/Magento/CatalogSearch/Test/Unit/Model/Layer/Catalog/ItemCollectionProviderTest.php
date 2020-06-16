<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Layer\Catalog;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogSearch\Model\Layer\Category\ItemCollectionProvider;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;

class ItemCollectionProviderTest extends TestCase
{
    public function testGetCollection()
    {
        $categoryMock = $this->createMock(Category::class);

        $collectionMock = $this->createMock(Collection::class);
        $collectionMock->expects($this->once())->method('addCategoryFilter')->with($categoryMock);

        $collectionFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $collectionFactoryMock->expects($this->any())->method('create')->willReturn($collectionMock);

        $objectManager = new ObjectManagerHelper($this);
        $provider = $objectManager->getObject(
            ItemCollectionProvider::class,
            ['collectionFactory' => $collectionFactoryMock]
        );

        $provider->getCollection($categoryMock);
    }
}
