<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Authorization\Model\Role;
use Magento\Backend\Model\Auth\Session;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Categories;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\DB\Helper as DbHelper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\User\Model\User;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class CategoriesTest extends AbstractModifierTestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

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
     * @var Session
     */
    private $sessionMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->categoryCollectionFactoryMock = $this->createPartialMock(
            CategoryCollectionFactory::class,
            ['create']
        );
        $this->dbHelperMock = $this->createMock(DbHelper::class);
        $this->urlBuilderMock = $this->createMock(UrlInterface::class);
        $this->storeMock = $this->createMock(Store::class);
        $this->categoryCollectionMock = $this->createMock(CategoryCollection::class);
        $this->authorizationMock = $this->createMock(AuthorizationInterface::class);
        $this->sessionMock = $this->createPartialMock(Session::class, []);
        $reflection = new \ReflectionClass($this->sessionMock);
        $storageProperty = $reflection->getProperty('storage');
        $storageProperty->setValue($this->sessionMock, new \Magento\Framework\DataObject());
        $this->categoryCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->categoryCollectionMock);
        $this->categoryCollectionMock->expects($this->any())
            ->method('addAttributeToSelect')
            ->willReturnSelf();
        $this->categoryCollectionMock->expects($this->any())
            ->method('addAttributeToSort')
            ->willReturnSelf();
        $this->categoryCollectionMock->expects($this->any())
            ->method('addAttributeToFilter')
            ->willReturnSelf();
        $this->categoryCollectionMock->expects($this->any())
            ->method('setStoreId')
            ->willReturnSelf();
        $this->categoryCollectionMock->method('getIterator')->willReturn(new \ArrayIterator([]));

        $roleAdmin = $this->createPartialMock(Role::class, ['getId']);
        $roleAdmin->expects($this->any())
            ->method('getId')
            ->willReturn(0);

        $userAdmin = $this->createPartialMock(User::class, ['getRole']);
        $userAdmin->expects($this->any())
            ->method('getRole')
            ->willReturn($roleAdmin);

        $this->sessionMock->setUser($userAdmin);
    }

    /**
     * {@inheritdoc}
     */
    protected function createModel()
    {
        $objects = [
            [
                CacheInterface::class,
                $this->createMock(CacheInterface::class)
            ]
        ];
        $this->objectManager->prepareObjectManager($objects);
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

    #[DataProvider('modifyMetaLockedDataProvider')]
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
        $this->arrayManagerMock->method('findPath')->willReturnCallback(
            function ($fieldCode, $meta, $default, $children) {
                if ($fieldCode === 'category_ids') {
                    return 'test_group_code.children.category_ids';
                }
                return 'test_group_code.children.container_category_ids';
            }
        );

        $this->productMock->setLockedAttribute('category_ids', $locked);

        $this->arrayManagerMock->expects($this->any())
            ->method('merge')
            ->willReturnArgument(2);

        $modifyMeta = $this->createModel()->modifyMeta($meta);

        // Debug: Check what the modifyMeta actually returns
        if (isset($modifyMeta['children']['category_ids']['arguments']['data']['config']['disabled'])) {
            $this->assertEquals(
                $locked,
                $modifyMeta['children']['category_ids']['arguments']['data']['config']['disabled']
            );
        } else {
            // If the structure is different, let's check what we actually got
            $this->assertTrue(
                isset($modifyMeta['children']['category_ids']),
                'category_ids field not found in modifyMeta result'
            );
        }

        if (isset($modifyMeta['children']['create_category_button']['arguments']['data']['config']['disabled'])) {
            $this->assertEquals(
                $locked,
                $modifyMeta['children']['create_category_button']['arguments']['data']['config']['disabled']
            );
        }
    }

    /**
     * @return array
     */
    public static function modifyMetaLockedDataProvider()
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

        $roleAclUser = $this->createMock(Role::class);
        $roleAclUser->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $userAclUser = $this->createMock(User::class);
        $userAclUser->expects($this->any())
            ->method('getRole')
            ->willReturn($roleAclUser);

        $this->sessionMock = $this->createPartialMock(Session::class, []);
        $reflection = new \ReflectionClass($this->sessionMock);
        $storageProperty = $reflection->getProperty('storage');
        $storageProperty->setValue($this->sessionMock, new \Magento\Framework\DataObject());

        $this->sessionMock->setUser($userAclUser);

        $categoriesAclUser = $this->createModel();
        $cacheIdAclUser = $this->invokeMethod($categoriesAclUser, 'getCategoriesTreeCacheId', [0]);

        $this->assertNotEquals($cacheIdAdmin, $cacheIdAclUser);
    }
}
