<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\App\Config\Source;

use Magento\Framework\App\DeploymentConfig;
use Magento\Store\App\Config\Source\RuntimeConfigSource;
use Magento\Store\Model\Group;
use Magento\Store\Model\GroupFactory;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory;
use Magento\Store\Model\ResourceModel\Group\CollectionFactory as GroupCollectionFactory;
use Magento\Store\Model\ResourceModel\Store\CollectionFactory as StoreCollectionFactory;
use Magento\Store\Model\ResourceModel\Website\Collection as WebsiteCollection;
use Magento\Store\Model\ResourceModel\Group\Collection as GroupCollection;
use Magento\Store\Model\ResourceModel\Store\Collection as StoreCollection;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\Website;
use Magento\Store\Model\WebsiteFactory;

/**
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RuntimeConfigSourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $websiteCollectionFactory;

    /**
     * @var GroupCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $groupCollectionFactory;

    /**
     * @var StoreCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeCollectionFactory;

    /**
     * @var WebsiteCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $websiteCollection;

    /**
     * @var GroupCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $groupCollection;

    /**
     * @var StoreCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeCollection;

    /**
     * @var WebsiteFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $websiteFactory;

    /**
     * @var GroupFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $groupFactory;

    /**
     * @var StoreFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeFactory;

    /**
     * @var Website|\PHPUnit_Framework_MockObject_MockObject
     */
    private $website;

    /**
     * @var Group|\PHPUnit_Framework_MockObject_MockObject
     */
    private $group;

    /**
     * @var Store|\PHPUnit_Framework_MockObject_MockObject
     */
    private $store;

    /**
     * @var DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfig;

    /**
     * @var RuntimeConfigSource
     */
    private $configSource;

    public function setUp()
    {
        $this->data = [
            'group' => [
                'code' => 'myGroup',
                'data' => [
                    'name' => 'My Group',
                    'group_id' => $this->data['group']['code']
                ]
            ],
            'website' => [
                'code' => 'myWebsite',
                'data' => [
                    'name' => 'My Website',
                    'website_code' => $this->data['website']['code']
                ]
            ],
            'store' => [
                'code' => 'myStore',
                'data' => [
                    'name' => 'My Store',
                    'store_code' => $this->data['store']['code']
                ]
            ],
        ];
        $this->websiteCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->groupCollectionFactory = $this->getMockBuilder(GroupCollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->storeCollectionFactory = $this->getMockBuilder(StoreCollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->websiteCollection = $this->getMockBuilder(WebsiteCollection::class)
            ->disableOriginalConstructor()
            ->setMethods(['setLoadDefault', 'getIterator'])
            ->getMock();
        $this->groupCollection = $this->getMockBuilder(GroupCollection::class)
            ->disableOriginalConstructor()
            ->setMethods(['setLoadDefault', 'getIterator'])
            ->getMock();
        $this->storeCollection = $this->getMockBuilder(StoreCollection::class)
            ->disableOriginalConstructor()
            ->setMethods(['setLoadDefault', 'getIterator'])
            ->getMock();

        $this->websiteFactory = $this->getMockBuilder(WebsiteFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->groupFactory = $this->getMockBuilder(GroupFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->storeFactory = $this->getMockBuilder(StoreFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->website = $this->getMockBuilder(Website::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->group = $this->getMockBuilder(Group::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->deploymentConfig = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configSource = new RuntimeConfigSource(
            $this->websiteCollectionFactory,
            $this->groupCollectionFactory,
            $this->storeCollectionFactory,
            $this->websiteFactory,
            $this->groupFactory,
            $this->storeFactory,
            $this->deploymentConfig
        );
    }

    /**
     * @param string $path
     * @dataProvider getDataProvider
     * @return void
     */
    public function testGet($path)
    {
        $this->deploymentConfig->expects($this->once())
            ->method('get')
            ->with('db')
            ->willReturn(true);
        $this->prepareWebsites($path);
        $this->prepareGroups($path);
        $this->prepareStores($path);
        $this->assertEquals($this->getExpectedResult($path), $this->configSource->get($path));
    }

    private function getExpectedResult($path)
    {
        switch ($this->getScope($path)) {
            case 'websites':
                $result = $this->data['website']['data'];
                break;
            case 'groups':
                $result = $this->data['group']['data'];
                break;
            case 'stores':
                $result = $this->data['store']['data'];
                break;
            default:
                $result = [
                    'websites' => [
                        $this->data['website']['code'] => $this->data['website']['data']
                    ],
                    'groups' => [
                        $this->data['group']['code'] => $this->data['group']['data']
                    ],
                    'stores' => [
                        $this->data['store']['code'] => $this->data['store']['data']
                    ],
                ];
                break;
        }
        return $result;
    }

    private function prepareStores($path)
    {
        $scope = $this->getScope($path);
        if ($scope == 'stores' || $scope == 'default') {
            if ($this->getScopeCode($path)) {
                $this->storeFactory->expects($this->once())
                    ->method('create')
                    ->willReturn($this->store);
                $this->store->expects($this->once())
                    ->method('load')
                    ->with($this->data['store']['code'], 'code')
                    ->willReturnSelf();
            } else {
                $this->storeCollectionFactory->expects($this->once())
                    ->method('create')
                    ->willReturn($this->storeCollection);
                $this->storeCollection->expects($this->once())
                    ->method('setLoadDefault')
                    ->with(true)
                    ->willReturnSelf();
                $this->storeCollection->expects($this->once())
                    ->method('getIterator')
                    ->willReturn(new \ArrayIterator([$this->store]));
                $this->store->expects($this->once())
                    ->method('getCode')
                    ->willReturn($this->data['store']['code']);
            }
            $this->store->expects($this->once())
                ->method('getData')
                ->willReturn($this->data['store']['data']);
        }
    }

    private function prepareGroups($path)
    {
        $scope = $this->getScope($path);
        if ($scope == 'groups' || $scope == 'default') {
            if ($this->getScopeCode($path)) {
                $this->groupFactory->expects($this->once())
                    ->method('create')
                    ->willReturn($this->group);
                $this->group->expects($this->once())
                    ->method('load')
                    ->with($this->data['group']['code'])
                    ->willReturnSelf();
            } else {
                $this->groupCollectionFactory->expects($this->once())
                    ->method('create')
                    ->willReturn($this->groupCollection);
                $this->groupCollection->expects($this->once())
                    ->method('setLoadDefault')
                    ->with(true)
                    ->willReturnSelf();
                $this->groupCollection->expects($this->once())
                    ->method('getIterator')
                    ->willReturn(new \ArrayIterator([$this->group]));
                $this->group->expects($this->once())
                    ->method('getId')
                    ->willReturn($this->data['group']['code']);
            }
            $this->group->expects($this->once())
                ->method('getData')
                ->willReturn($this->data['group']['data']);
        }
    }

    private function prepareWebsites($path)
    {
        $scope = $this->getScope($path);
        if ($scope == 'websites' || $scope == 'default') {
            if ($this->getScopeCode($path)) {
                $this->websiteFactory->expects($this->once())
                    ->method('create')
                    ->willReturn($this->website);
                $this->website->expects($this->once())
                    ->method('load')
                    ->with($this->data['website']['code'])
                    ->willReturnSelf();
            } else {
                $this->websiteCollectionFactory->expects($this->once())
                    ->method('create')
                    ->willReturn($this->websiteCollection);
                $this->websiteCollection->expects($this->once())
                    ->method('setLoadDefault')
                    ->with(true)
                    ->willReturnSelf();
                $this->websiteCollection->expects($this->once())
                    ->method('getIterator')
                    ->willReturn(new \ArrayIterator([$this->website]));
                $this->website->expects($this->once())
                    ->method('getCode')
                    ->willReturn($this->data['website']['code']);
            }
            $this->website->expects($this->once())
                ->method('getData')
                ->willReturn($this->data['website']['data']);
        }
    }

    private function getScopeCode($path)
    {
        return implode('/', array_slice(explode('/', $path), 1, 1));
    }

    private function getScope($path)
    {
        return implode('/', array_slice(explode('/', $path), 0, 1));
    }

    /**
     * @return array
     */
    public function getDataProvider()
    {
        return [
            ['websites/myWebsite'],
            ['groups/myGroup'],
            ['stores/myStore'],
            ['default']
        ];
    }
}
