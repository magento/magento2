<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Categories;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\DB\Helper as DbHelper;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\Backend\Model\Auth\Session;
use Magento\Authorization\Model\Role;
use Magento\User\Model\User;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoriesTest extends AbstractModifierTest
{
    /**
     * @var CategoryCollectionFactory|MockObject
     */
    protected $categoryCollectionFactoryMock;

    /**
     * @var DbHelper|MockObject
     */
    protected $dbHelperMock;

    /**
     * @var UrlInterface|MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var Store|MockObject
     */
    protected $storeMock;

    /**
     * @var CategoryCollection|MockObject
     */
    protected $categoryCollectionMock;

    /**
     * @var AuthorizationInterface|MockObject
     */
    private $authorizationMock;

    /**
     * @var Session|MockObject
     */
    private $sessionMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->categoryCollectionFactoryMock = $this->getMockBuilder(CategoryCollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbHelperMock = $this->getMockBuilder(DbHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlBuilderMock = $this->getMockBuilder(UrlInterface::class)
            ->getMockForAbstractClass();
        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->categoryCollectionMock = $this->getMockBuilder(CategoryCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->authorizationMock = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->setMethods(['getUser'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->categoryCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->categoryCollectionMock);
        $this->categoryCollectionMock->expects($this->any())
            ->method('addAttributeToSelect')
            ->willReturnSelf();
        $this->categoryCollectionMock->expects($this->any())
            ->method('addAttributeToFilter')
            ->willReturnSelf();
        $this->categoryCollectionMock->expects($this->any())
            ->method('setStoreId')
            ->willReturnSelf();
        $this->categoryCollectionMock->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([]));

        $roleAdmin = $this->getMockBuilder(Role::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $roleAdmin->expects($this->any())
            ->method('getId')
            ->willReturn(0);

        $userAdmin = $this->getMockBuilder(User::class)
            ->setMethods(['getRole'])
            ->disableOriginalConstructor()
            ->getMock();
        $userAdmin->expects($this->any())
            ->method('getRole')
            ->willReturn($roleAdmin);

        $this->sessionMock->expects($this->any())
            ->method('getUser')
            ->willReturn($userAdmin);
    }

    /**
     * {@inheritdoc}
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(
            Categories::class,
            [
                'locator' => $this->locatorMock,
                'categoryCollectionFactory' => $this->categoryCollectionFactoryMock,
                'arrayManager' => $this->arrayManagerMock,
                'authorization' => $this->authorizationMock,
                'session' => $this->sessionMock
            ]
        );
    }

    /**
     * @param object $object
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws \ReflectionException
     */
    private function invokeMethod($object, $method, $args = [])
    {
        $class = new \ReflectionClass(Categories::class);
        $method = $class->getMethod($method);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $args);
    }

    public function testModifyData()
    {
        $this->assertSame([], $this->getModel()->modifyData([]));
    }

    public function testModifyMeta()
    {
        $groupCode = 'test_group_code';
        $meta = [
            $groupCode => [
                'children' => [
                    'category_ids' => [
                        'sortOrder' => 10,
                    ],
                ],
            ],
        ];

        $this->assertArrayHasKey($groupCode, $this->getModel()->modifyMeta($meta));
    }

    /**
     * @param bool $locked
     * @dataProvider modifyMetaLockedDataProvider
     */
    public function testModifyMetaLocked($locked)
    {
        $groupCode = 'test_group_code';
        $meta = [
            $groupCode => [
                'children' => [
                    'category_ids' => [
                        'sortOrder' => 10,
                    ],
                ],
            ],
        ];
        $this->authorizationMock->expects($this->exactly(2))
            ->method('isAllowed')
            ->willReturn(true);
        $this->arrayManagerMock->expects($this->any())
            ->method('findPath')
            ->willReturn('path');

        $this->productMock->expects($this->any())
            ->method('isLockedAttribute')
            ->willReturn($locked);

        $this->arrayManagerMock->expects($this->any())
            ->method('merge')
            ->willReturnArgument(2);

        $modifyMeta = $this->createModel()->modifyMeta($meta);
        $this->assertEquals(
            $locked,
            $modifyMeta['children']['category_ids']['arguments']['data']['config']['disabled']
        );
        $this->assertEquals(
            $locked,
            $modifyMeta['children']['create_category_button']['arguments']['data']['config']['disabled']
        );
    }

    /**
     * @return array
     */
    public function modifyMetaLockedDataProvider()
    {
        return [[true], [false]];
    }

    /**
     * Asserts that a user with an ACL role ID of 0 and a user with an ACL role ID of 1 do not have the same cache IDs
     * Assumes a store ID of 0
     *
     * @throws \ReflectionException
     */
    public function testAclCacheIds()
    {
        $categoriesAdmin = $this->createModel();
        $cacheIdAdmin = $this->invokeMethod($categoriesAdmin, 'getCategoriesTreeCacheId', [0]);

        $roleAclUser = $this->getMockBuilder(Role::class)
            ->disableOriginalConstructor()
            ->getMock();
        $roleAclUser->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $userAclUser = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();
        $userAclUser->expects($this->any())
            ->method('getRole')
            ->will($this->returnValue($roleAclUser));

        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->setMethods(['getUser'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->sessionMock->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($userAclUser));

        $categoriesAclUser = $this->createModel();
        $cacheIdAclUser = $this->invokeMethod($categoriesAclUser, 'getCategoriesTreeCacheId', [0]);

        $this->assertNotEquals($cacheIdAdmin, $cacheIdAclUser);
    }
}
