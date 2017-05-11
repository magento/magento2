<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Related;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductLinkRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Ui\DataProvider\Product\Related\AbstractDataProvider;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

/**
 * Class AbstractDataProviderTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractDataProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepositoryMock;

    /**
     * @var ProductLinkRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productLinkRepositoryMock;

    /**
     * @var ProductInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionMock;

    /**
     * @return AbstractDataProvider
     */
    abstract protected function getModel();

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();
        $this->productRepositoryMock = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->productLinkRepositoryMock = $this->getMockBuilder(ProductLinkRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->productMock = $this->getMockBuilder(ProductInterface::class)
            ->getMockForAbstractClass();
        $this->collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->productRepositoryMock->expects($this->any())
            ->method('getById')
            ->willReturn($this->productMock);
        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->collectionMock);
    }

    public function testGetCollection()
    {
        $this->collectionMock->expects($this->once())
            ->method('addAttributeToFilter');
        $this->productLinkRepositoryMock->expects($this->once())
            ->method('getList')
            ->willReturn([]);
        $this->requestMock->expects($this->exactly(2))
            ->method('getParam')
            ->willReturn(1);

        $this->assertInstanceOf(Collection::class, $this->getModel()->getCollection());
    }
}
