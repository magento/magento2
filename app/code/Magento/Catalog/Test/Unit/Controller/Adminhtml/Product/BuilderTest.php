<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product;

use \Magento\Catalog\Controller\Adminhtml\Product\Builder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreFactory;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Registry;
use Magento\Cms\Model\Wysiwyg\Config as WysiwygConfig;
use Magento\Framework\App\Request\Http;

/**
 * Class BuilderTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $wysiwygConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var StoreFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeFactoryMock;

    /**
     * @var StoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->loggerMock = $this->getMock(LoggerInterface::class);
        $this->productFactoryMock = $this->getMock(ProductFactory::class, ['create'], [], '', false);
        $this->registryMock = $this->getMock(Registry::class, [], [], '', false);
        $this->wysiwygConfigMock = $this->getMock(WysiwygConfig::class, ['setStoreId'], [], '', false);
        $this->requestMock = $this->getMock(Http::class, [], [], '', false);
        $methods = ['setStoreId', 'setData', 'load', '__wakeup', 'setAttributeSetId', 'setTypeId'];
        $this->productMock = $this->getMock('Magento\Catalog\Model\Product', $methods, [], '', false);
        $this->storeFactoryMock = $this->getMockBuilder(StoreFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->setMethods(['load'])
            ->getMockForAbstractClass();

        $this->storeFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->storeMock);

        $this->builder = $this->objectManager->getObject(Builder::class, [
            'productFactory' => $this->productFactoryMock,
            'logger' => $this->loggerMock,
            'registry' => $this->registryMock,
            'wysiwygConfig' => $this->wysiwygConfigMock,
            'storeFactory' => $this->storeFactoryMock,
        ]);
    }

    public function testBuildWhenProductExistAndPossibleToLoadProduct()
    {
        $valueMap = [
            ['id', null, 2],
            ['store', 0, 'some_store'],
            ['type', null, 'type_id'],
            ['set', null, 3],
            ['store', null, 'store'],
        ];
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap($valueMap);
        $this->productFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->productMock));
        $this->productMock->expects($this->once())
            ->method('setStoreId')
            ->with('some_store')
            ->willReturnSelf();
        $this->productMock->expects($this->never())
            ->method('setTypeId');
        $this->productMock->expects($this->once())
            ->method('load')
            ->with(2)
            ->will($this->returnSelf());
        $this->productMock->expects($this->once())
            ->method('setAttributeSetId')
            ->with(3)
            ->will($this->returnSelf());
        $registryValueMap = [
            ['product', $this->productMock, $this->registryMock],
            ['current_product', $this->productMock, $this->registryMock],
        ];
        $this->registryMock->expects($this->any())
            ->method('register')
            ->willReturn($registryValueMap);
        $this->wysiwygConfigMock->expects($this->once())
            ->method('setStoreId')
            ->with('store');
        $this->assertEquals($this->productMock, $this->builder->build($this->requestMock));
    }

    public function testBuildWhenImpossibleLoadProduct()
    {
        $valueMap = [
            ['id', null, 15],
            ['store', 0, 'some_store'],
            ['type', null, 'type_id'],
            ['set', null, 3],
            ['store', null, 'store'],
        ];
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->will($this->returnValueMap($valueMap));
        $this->productFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())
            ->method('setStoreId')
            ->with('some_store')
            ->willReturnSelf();
        $this->productMock->expects($this->once())
            ->method('setTypeId')
            ->with(\Magento\Catalog\Model\Product\Type::DEFAULT_TYPE)
            ->willReturnSelf();
        $this->productMock->expects($this->once())
            ->method('load')
            ->with(15)
            ->willThrowException(new \Exception());
        $this->loggerMock->expects($this->once())
            ->method('critical');
        $this->productMock->expects($this->once())
            ->method('setAttributeSetId')
            ->with(3)
            ->will($this->returnSelf());
        $registryValueMap = [
            ['product', $this->productMock, $this->registryMock],
            ['current_product', $this->productMock, $this->registryMock],
        ];
        $this->registryMock->expects($this->any())
            ->method('register')
            ->will($this->returnValueMap($registryValueMap));
        $this->wysiwygConfigMock->expects($this->once())
            ->method('setStoreId')
            ->with('store');
        $this->assertEquals($this->productMock, $this->builder->build($this->requestMock));
    }

    public function testBuildWhenProductNotExist()
    {
        $valueMap = [
            ['id', null, null],
            ['store', 0, 'some_store'],
            ['type', null, 'type_id'],
            ['set', null, 3],
            ['store', null, 'store'],
        ];
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->will($this->returnValueMap($valueMap));
        $this->productFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())
            ->method('setStoreId')
            ->with('some_store')
            ->willReturnSelf();
        $productValueMap = [
            ['type_id', $this->productMock],
            [\Magento\Catalog\Model\Product\Type::DEFAULT_TYPE, $this->productMock],
        ];
        $this->productMock->expects($this->any())
            ->method('setTypeId')
            ->willReturnMap($productValueMap);
        $this->productMock->expects($this->never())
            ->method('load');
        $this->productMock->expects($this->once())
            ->method('setAttributeSetId')
            ->with(3)
            ->will($this->returnSelf());
        $registryValueMap = [
            ['product', $this->productMock, $this->registryMock],
            ['current_product', $this->productMock, $this->registryMock],
        ];
        $this->registryMock->expects($this->any())
            ->method('register')
            ->will($this->returnValueMap($registryValueMap));
        $this->wysiwygConfigMock->expects($this->once())
            ->method('setStoreId')
            ->with('store');
        $this->assertEquals($this->productMock, $this->builder->build($this->requestMock));
    }
}
