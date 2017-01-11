<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Category\Plugin\Category;

use Magento\CatalogUrlRewrite\Model\Category\Plugin\Category\Remove as CategoryRemovePlugin;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResourceModel;
use Magento\Catalog\Model\Category;

class RemoveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var UrlPersistInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlPersistMock;

    /**
     * @var ChildrenCategoriesProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $childrenCategoriesProviderMock;

    /**
     * @var CategoryResourceModel|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectMock;

    /**
     * @var Category|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectMock;

    /** @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $serializerMock;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->urlPersistMock = $this->getMockBuilder(UrlPersistInterface::class)
            ->getMockForAbstractClass();
        $this->childrenCategoriesProviderMock = $this->getMockBuilder(ChildrenCategoriesProvider::class)
            ->getMock();
        $this->subjectMock = $this->getMockBuilder(CategoryResourceModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectMock = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializerMock = $this->getMock(\Magento\Framework\Serialize\SerializerInterface::class, [], [], '', false);
    }

    public function testAroundDelete()
    {
        $closureSubject = $this->subjectMock;
        $proceed  = function () use ($closureSubject) {
            return $closureSubject;
        };
        $plugin = $this->objectManager->getObject(
            CategoryRemovePlugin::class,
            [
                'urlPersist' => $this->urlPersistMock,
                'childrenCategoriesProvider' => $this->childrenCategoriesProviderMock,
                'serializer' => $this->serializerMock
            ]
        );
        $this->childrenCategoriesProviderMock->expects($this->once())
            ->method('getChildrenIds')
            ->with($this->objectMock, true)
            ->willReturn([]);
        $this->objectMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->urlPersistMock->expects($this->exactly(2))
            ->method('deleteByData');
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with(['category_id' => 1]);
        $this->assertSame(
            $this->subjectMock,
            $plugin->aroundDelete($this->subjectMock, $proceed, $this->objectMock)
        );
    }
}
