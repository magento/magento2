<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Category;

use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Controller\Adminhtml\Category\RefreshPath;
use Magento\Backend\App\Action\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
<<<<<<< HEAD
 * Test for class Magento\Catalog\Controller\Adminhtml\Category\RefreshPath.
=======
 * Test for class RefreshPath.
>>>>>>> upstream/2.2-develop
 */
class RefreshPathTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJsonFactoryMock;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
<<<<<<< HEAD
     * @inheritDoc
=======
     * {@inheritDoc}
>>>>>>> upstream/2.2-develop
     */
    protected function setUp()
    {
        $this->resultJsonFactoryMock = $this->getMockBuilder(JsonFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create', 'setData'])
            ->getMock();

        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRequest'])
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
<<<<<<< HEAD
    private function setObjectProperty($object, string $propertyName, $value) : void
=======
    private function setObjectProperty($object, string $propertyName, $value)
>>>>>>> upstream/2.2-develop
    {
        $reflectionClass = new \ReflectionClass($object);
        $reflectionProperty = $reflectionClass->getProperty($propertyName);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
    }

    /**
     * @return void
     */
<<<<<<< HEAD
    public function testExecute() : void
=======
    public function testExecute()
>>>>>>> upstream/2.2-develop
    {
        $value = ['id' => 3, 'path' => '1/2/3', 'parentId' => 2];
        $result = '{"id":3,"path":"1/2/3","parentId":"2"}';

        $requestMock = $this->getMockForAbstractClass(\Magento\Framework\App\RequestInterface::class);

        $refreshPath = $this->getMockBuilder(RefreshPath::class)
            ->setMethods(['getRequest', 'create'])
            ->setConstructorArgs([
                $this->contextMock,
                $this->resultJsonFactoryMock,
            ])->getMock();

        $refreshPath->expects($this->any())->method('getRequest')->willReturn($requestMock);
        $requestMock->expects($this->any())->method('getParam')->with('id')->willReturn($value['id']);

        $categoryMock = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPath', 'getParentId', 'getResource'])
            ->getMock();

        $categoryMock->expects($this->any())->method('getPath')->willReturn($value['path']);
        $categoryMock->expects($this->any())->method('getParentId')->willReturn($value['parentId']);

        $categoryResource = $this->createMock(\Magento\Catalog\Model\ResourceModel\Category::class);

        $objectManagerMock = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->setObjectProperty($refreshPath, '_objectManager', $objectManagerMock);
        $this->setObjectProperty($categoryMock, '_resource', $categoryResource);

        $objectManagerMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Catalog\Model\Category::class)
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
<<<<<<< HEAD
    public function testExecuteWithoutCategoryId() : void
=======
    public function testExecuteWithoutCategoryId()
>>>>>>> upstream/2.2-develop
    {
        $requestMock = $this->getMockForAbstractClass(\Magento\Framework\App\RequestInterface::class);

        $refreshPath = $this->getMockBuilder(RefreshPath::class)
            ->setMethods(['getRequest', 'create'])
            ->setConstructorArgs([
                $this->contextMock,
                $this->resultJsonFactoryMock,
            ])->getMock();

        $refreshPath->expects($this->any())->method('getRequest')->willReturn($requestMock);
        $requestMock->expects($this->any())->method('getParam')->with('id')->willReturn(null);

        $objectManagerMock = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->setObjectProperty($refreshPath, '_objectManager', $objectManagerMock);

        $objectManagerMock->expects($this->never())
            ->method('create')
            ->with(\Magento\Catalog\Model\Category::class)
            ->willReturnSelf();

        $refreshPath->execute();
    }
}
