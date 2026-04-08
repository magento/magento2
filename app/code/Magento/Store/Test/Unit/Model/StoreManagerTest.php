<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Model;

use Magento\Framework\App\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Cache\CacheConstants;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\GroupRepositoryInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\Group;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\StoreResolver;
use Magento\Store\Model\Website;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StoreManagerTest extends TestCase
{
    /**
     * @var StoreManager
     */
    protected $model;

    /**
     * @var StoreRepositoryInterface|MockObject
     */
    protected $storeRepositoryMock;

    /**
     * @var StoreResolverInterface|MockObject
     */
    protected $storeResolverMock;

    /**
     * @var FrontendInterface|MockObject
     */
    private $cache;

    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->storeRepositoryMock = $this->createMock(StoreRepositoryInterface::class);
        $this->storeResolverMock = $this->createMock(StoreResolverInterface::class);
        $this->cache = $this->createMock(FrontendInterface::class);
        $this->scopeConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->websiteRepository = $this->createMock(WebsiteRepositoryInterface::class);
        $this->groupRepository = $this->createMock(GroupRepositoryInterface::class);

        $this->model = $objectManager->getObject(
            StoreManager::class,
            [
                'storeRepository' => $this->storeRepositoryMock,
                'storeResolver' => $this->storeResolverMock,
                'cache' => $this->cache,
                'scopeConfig' => $this->scopeConfig,
                'websiteRepository' => $this->websiteRepository,
                'groupRepository' => $this->groupRepository
            ]
        );
    }

    public function testGetStoreEmptyParameter()
    {
        $storeId = 1;
        $storeMock = $this->createMock(StoreInterface::class);
        $this->storeResolverMock->expects($this->any())->method('getCurrentStoreId')->willReturn($storeId);
        $this->storeRepositoryMock->expects($this->atLeastOnce())
            ->method('getById')
            ->with($storeId)
            ->willReturn($storeMock);
        $this->assertInstanceOf(StoreInterface::class, $this->model->getStore());
        $this->assertEquals($storeMock, $this->model->getStore());
    }

    public function testGetStoreStringParameter()
    {
        $storeId = 'store_code';
        $storeMock = $this->createMock(StoreInterface::class);
        $this->storeRepositoryMock->expects($this->atLeastOnce())
            ->method('get')
            ->with($storeId)
            ->willReturn($storeMock);
        $actualStore = $this->model->getStore($storeId);
        $this->assertInstanceOf(StoreInterface::class, $actualStore);
        $this->assertEquals($storeMock, $actualStore);
    }

    public function testGetStoreObjectStoreParameter()
    {
        $storeMock = $this->createMock(StoreInterface::class);
        $actualStore = $this->model->getStore($storeMock);
        $this->assertInstanceOf(StoreInterface::class, $actualStore);
        $this->assertEquals($storeMock, $actualStore);
    }

    public function testReinitStores()
    {
        $this->cache->expects($this->once())->method('clean')->with(
            CacheConstants::CLEANING_MODE_MATCHING_ANY_TAG,
            [StoreResolver::CACHE_TAG, Store::CACHE_TAG, Website::CACHE_TAG, Group::CACHE_TAG]
        );
        $this->scopeConfig->expects($this->once())->method('clean');
        $this->storeRepositoryMock->expects($this->once())->method('clean');
        $this->websiteRepository->expects($this->once())->method('clean');
        $this->groupRepository->expects($this->once())->method('clean');

        $this->model->reinitStores();
    }

    #[DataProvider('getStoresDataProvider')]
    public function testGetStores($storesList, $withDefault, $codeKey, $expectedStores)
    {
        $storesListFinal = [];
        foreach ($storesList as $list) {
            $storesListFinal[] = $list($this);
        }

        $expectedStoresFinal = [];
        foreach ($expectedStores as $key => $value) {
            if (is_callable($value)) {
                $expectedStoresFinal[$key] = $value($this);
            }
        }

        $this->storeRepositoryMock->expects($this->any())->method('getList')->willReturn($storesListFinal);
        $this->assertEquals($expectedStoresFinal, $this->model->getStores($withDefault, $codeKey));
    }

    protected function getMockForStoreInterfaceClass($idReturn, $codeReturn)
    {
        $storeMock = $this->createMock(StoreInterface::class);
        $storeMock->expects($this->any())->method('getId')->willReturn($idReturn);
        $storeMock->expects($this->any())->method('getCode')->willReturn($codeReturn);
        return $storeMock;
    }

    /**
     * @return array
     */
    public static function getStoresDataProvider()
    {
        $defaultStoreMock = static fn (self $testCase) =>
            $testCase->getMockForStoreInterfaceClass(0, 'default');
        $storeMock = static fn (self $testCase) =>
            $testCase->getMockForStoreInterfaceClass(1, 'first_store');

        return [
            'withoutDefaultAndId' => [
                'storesList' => [$defaultStoreMock, $storeMock],
                'withDefault' => false,
                'codeKey' => false,
                'expectedStores' =>  [1 => $storeMock]
            ],
            'withoutDefaultAndCodeKey' => [
                'storesList' => [$defaultStoreMock,$storeMock],
                'withDefault' => false,
                'codeKey' => true,
                'expectedStores' =>  ['first_store' => $storeMock]
            ],
            'withDefaultAndId' => [
                'storesList' => [$defaultStoreMock,$storeMock],
                'withDefault' => true,
                'codeKey' => false,
                'expectedStores' =>  [0 => $defaultStoreMock, 1 => $storeMock]
            ],
            'withDefaultAndCodeKey' => [
                'storesList' => [$defaultStoreMock,$storeMock],
                'withDefault' => true,
                'codeKey' => true,
                'expectedStores' =>  ['default' => $defaultStoreMock, 'first_store' => $storeMock]
            ],
        ];
    }
}
