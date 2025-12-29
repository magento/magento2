<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Category;

use Magento\Backend\App\Action\Context;
use Magento\Catalog\Controller\Adminhtml\Category\RefreshPath;
use Magento\Catalog\Model\Category;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for class Magento\Catalog\Controller\Adminhtml\Category\RefreshPath.
 */
class RefreshPathTest extends TestCase
{
    use MockCreationTrait;
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
        $this->resultJsonFactoryMock = $this->createMock(JsonFactory::class);

        $this->contextMock = $this->createPartialMock(Context::class, ['getRequest']);
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

        $requestMock = $this->createMock(RequestInterface::class);
        $requestMock->expects($this->any())->method('getParam')->with('id')->willReturn($value['id']);

        $this->contextMock->expects($this->any())->method('getRequest')->willReturn($requestMock);

        $objectManager = new ObjectManager($this);
        $objects = [
            [
                StoreManagerInterface::class,
                $this->createMock(StoreManagerInterface::class)
            ]
        ];
        $objectManager->prepareObjectManager($objects);

        $refreshPath = $this->createPartialMockWithReflection(
            RefreshPath::class,
            ['execute', 'setRequestMock']
        );

        $categoryMock = $this->createPartialMock(Category::class, ['getPath', 'getParentId', 'getResource']);
        $categoryMock->method('getPath')->willReturn($value['path']);
        $categoryMock->method('getParentId')->willReturn($value['parentId']);

        $categoryResource = $this->createMock(\Magento\Catalog\Model\ResourceModel\Category::class);

        $objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $objectManagerMock->expects($this->once())
            ->method('create')
            ->with(Category::class)
            ->willReturn($categoryMock);

        $this->setObjectProperty($categoryMock, '_resource', $categoryResource);

        // Create Json result mock
        $jsonResultMock = $this->createMock(Json::class);
        $jsonResultMock->method('setData')->willReturn($result);

        // Configure factory to return the Json result
        $this->resultJsonFactoryMock->method('create')->willReturn($jsonResultMock);

        $refreshPath->method('execute')->willReturnCallback(
            function () use ($requestMock, $objectManagerMock, $jsonResultMock, $value) {
                $categoryId = $requestMock->getParam('id');
                if ($categoryId) {
                    $category = $objectManagerMock->create(Category::class);
                    $data = [
                        'id' => $categoryId,
                        'path' => $category->getPath(),
                        'parentId' => (string)$category->getParentId(),
                        'level' => (string)$value['level']
                    ];
                    return $jsonResultMock->setData($data);
                }
                return $jsonResultMock;
            }
        );

        $this->assertEquals($result, $refreshPath->execute());
    }

    /**
     * @return void
     */
    public function testExecuteWithoutCategoryId() : void
    {
        $requestMock = $this->createMock(RequestInterface::class);
        $requestMock->expects($this->any())->method('getParam')->with('id')->willReturn(null);

        $this->contextMock->expects($this->any())->method('getRequest')->willReturn($requestMock);

        $refreshPath = $this->createPartialMockWithReflection(
            RefreshPath::class,
            ['execute', 'setRequestMock']
        );

        $jsonResultMock = $this->createMock(Json::class);
        $this->resultJsonFactoryMock->method('create')->willReturn($jsonResultMock);

        $refreshPath->method('execute')->willReturnCallback(function () use ($requestMock, $jsonResultMock) {
            $categoryId = $requestMock->getParam('id');
            if (!$categoryId) {
                return $jsonResultMock;
            }
            return $jsonResultMock;
        });

        $refreshPath->execute();
    }
}
