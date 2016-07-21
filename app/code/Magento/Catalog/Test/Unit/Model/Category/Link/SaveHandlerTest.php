<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Category\Link;

use Magento\Catalog\Api\Data\CategoryLinkInterface;
use Magento\Catalog\Model\Category\Link\SaveHandler;
use Magento\Catalog\Model\ResourceModel\Product\CategoryLink;
use Magento\Framework\EntityManager\HydratorInterface;
use Magento\Framework\EntityManager\HydratorPool;
use Magento\Catalog\Model\Product;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class SaveHandlerTest
 */
class SaveHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SaveHandler
     */
    private $saveHandler;

    /**
     * @var CategoryLink|MockObject
     */
    private $productCategoryLink;

    /**
     * @var HydratorInterface|MockObject
     */
    private $hydrator;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->productCategoryLink = $this->getMockBuilder(CategoryLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->hydrator = $this->getMockBuilder(HydratorInterface::class)->getMockForAbstractClass();
        $hydratorPool = $this->getMockBuilder(HydratorPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $hydratorPool->expects(static::once())
            ->method('getHydrator')
            ->with(CategoryLinkInterface::class)
            ->willReturn($this->hydrator);
        $this->saveHandler = new SaveHandler($this->productCategoryLink, $hydratorPool);
    }

    public function testExecute()
    {
        $categoryLinks = [
            ['category_id' => 3, 'position' => 10],
            ['category_id' => 4, 'position' => 20]
        ];

        $this->hydrator->expects(static::any())
            ->method('extract')
            ->willReturnArgument(0);

        $extensionAttributes = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setCategoryLinks', 'getCategoryLinks'])
            ->getMockForAbstractClass();
        $extensionAttributes->expects(static::any())
            ->method('getCategoryLinks')
            ->willReturn($categoryLinks);

        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getExtensionAttributes'])
            ->getMock();
        $product->expects(static::once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);

        $this->productCategoryLink->expects(static::any())
            ->method('saveCategoryLinks')
            ->with($product, $categoryLinks);

        $entity = $this->saveHandler->execute($product);
        static::assertSame($product, $entity);
    }
}
