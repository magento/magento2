<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Category\Link;

use Magento\Catalog\Api\Data\CategoryLinkInterfaceFactory;
use Magento\Catalog\Model\Category\Link\ReadHandler;
use Magento\Catalog\Model\ResourceModel\Product\CategoryLink;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Catalog\Model\Product;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class ReadHandlerTest
 */
class ReadHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReadHandler
     */
    private $readHandler;

    /**
     * @var CategoryLinkInterfaceFactory|MockObject
     */
    private $categoryLinkFactory;

    /**
     * @var CategoryLink|MockObject
     */
    private $productCategoryLink;

    /**
     * @var DataObjectHelper|MockObject
     */
    private $dataObjectHelper;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->categoryLinkFactory = $this->getMockBuilder(CategoryLinkInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->productCategoryLink = $this->getMockBuilder(CategoryLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectHelper = $this->getMockBuilder(DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->readHandler = new ReadHandler(
            $this->categoryLinkFactory,
            $this->dataObjectHelper,
            $this->productCategoryLink
        );
    }

    public function testExecute()
    {
        $categoryLinks = [
            ['category_id' => 3, 'position' => 10],
            ['category_id' => 4, 'position' => 20]
        ];

        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getExtensionAttributes', 'setExtensionAttributes'])
            ->getMock();

        $extensionAttributes = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setCategoryLinks'])
            ->getMockForAbstractClass();

        $product->expects(static::once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);

        $product->expects(static::once())
            ->method('setExtensionAttributes')
            ->with($extensionAttributes);

        $this->productCategoryLink->expects(static::any())
            ->method('getCategoryLinks')
            ->with($product)
            ->willReturn($categoryLinks);

        $entity = $this->readHandler->execute($product);
        static::assertSame($product, $entity);
    }
}
