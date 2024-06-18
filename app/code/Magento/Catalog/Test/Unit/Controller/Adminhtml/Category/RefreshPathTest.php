<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Category;

use Magento\Backend\App\Action\Context;
use Magento\Catalog\Controller\Adminhtml\Category\RefreshPath;
use Magento\Catalog\Model\Category;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for class Magento\Catalog\Controller\Adminhtml\Category\RefreshPath.
 */
class RefreshPathTest extends TestCase
{
    /**
     * @var JsonFactory|MockObject
     */
    private $resultJsonFactoryMock;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->resultJsonFactoryMock = $this->getMockBuilder(JsonFactory::class)
            ->disableOriginalConstructor()
            ->addMethods(['setData'])
            ->onlyMethods(['create'])
            ->getMock();

        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRequest'])
            ->getMock();
    }

    /**
     * Sets object non-public property.
     *
     * @param mixed $object
     * @param string $propertyName
     * @param mixed $value
     *
     * @return void
     */
    private function setObjectProperty($object, string $propertyName, $value) : void
    {
        $reflectionClass = new \ReflectionClass($object);
        $reflectionProperty = $reflectionClass->getProperty($propertyName);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
    }

    /**
     * @return void
     */
    public function testExecute() : void
    {
        $value = ['id' => 3, 'path' => '1/2/3', 'parentId' => 2, 'level' => 2];
        $result = '{"id":3,"path":"1/2/3","parentId":"2","level":"2"}';

        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);

        $objectManager = new ObjectManager($this);
        $objects = [
            [
                StoreManagerInterface::class,
                $this->createMock(StoreManagerInterface::class)
            ]
        ];
        $objectManager->prepareObjectManager($objects);

        $refreshPath = $this->getMockBuilder(RefreshPath::class)
            ->addMethods(['create'])
            ->onlyMethods(['getRequest'])
            ->setConstructorArgs([
                $this->contextMock,
                $this->resultJsonFactoryMock,
            ])
            ->getMock();

        $refreshPath->expects($this->any())->method('getRequest')->willReturn($requestMock);
        $requestMock->expects($this->any())->method('getParam')->with('id')->willReturn($value['id']);

        $categoryMock = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPath', 'getParentId', 'getResource'])
            ->getMock();

        $categoryMock->expects($this->any())->method('getPath')->willReturn($value['path']);
        $categoryMock->expects($this->any())->method('getParentId')->willReturn($value['parentId']);

        $categoryResource = $this->createMock(\Magento\Catalog\Model\ResourceModel\Category::class);

        $objectManagerMock = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->addMethods(['create'])
            ->getMock();

        $this->setObjectProperty($refreshPath, '_objectManager', $objectManagerMock);
        $this->setObjectProperty($categoryMock, '_resource', $categoryResource);

        $objectManagerMock->expects($this->once())
            ->method('create')
            ->with(Category::class)
            ->willReturn($categoryMock);

        $this->resultJsonFactoryMock->expects($this->any())->method('create')->willReturnSelf();
        $this->resultJsonFactoryMock->expects($this->any())
            ->method('setData')
            ->with($value)
            ->willReturn($result);

        $this->assertEquals($result, $refreshPath->execute());
    }

    /**
     * @return void
     */
    public function testExecuteWithoutCategoryId() : void
    {
        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);

        $refreshPath = $this->getMockBuilder(RefreshPath::class)
            ->addMethods(['create'])
            ->onlyMethods(['getRequest'])
            ->setConstructorArgs([
                $this->contextMock,
                $this->resultJsonFactoryMock,
            ])->getMock();

        $refreshPath->expects($this->any())->method('getRequest')->willReturn($requestMock);
        $requestMock->expects($this->any())->method('getParam')->with('id')->willReturn(null);

        $objectManagerMock = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->addMethods(['create'])
            ->getMock();

        $this->setObjectProperty($refreshPath, '_objectManager', $objectManagerMock);

        $objectManagerMock->expects($this->never())
            ->method('create')
            ->with(Category::class)
            ->willReturnSelf();

        $refreshPath->execute();
    }
}
