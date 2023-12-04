<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\PageCache\Model\Cache\Type;
use Magento\TestFramework\Helper\Bootstrap;

class WebsiteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Store\Model\Website
     */
    protected $_model;

    /**
     * @var TypeListInterface
     */
    private $typeList;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->typeList = $this->objectManager->create(TypeListInterface::class);
        $this->_model = $this->objectManager->create(\Magento\Store\Model\Website::class);
        $this->_model->load(1);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testLoadById()
    {
        $this->assertEquals(1, $this->_model->getId());
        $this->assertEquals('base', $this->_model->getCode());
        $this->assertEquals('Main Website', $this->_model->getName());
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testLoadByCode()
    {
        $this->_model->load('admin');
        $this->assertEquals(0, $this->_model->getId());
        $this->assertEquals('admin', $this->_model->getCode());
        $this->assertEquals('Admin', $this->_model->getName());
    }

    /**
     * @covers \Magento\Store\Model\Website::setGroups
     * @covers \Magento\Store\Model\Website::setStores
     * @covers \Magento\Store\Model\Website::getStores
     */
    public function testSetGroupsAndStores()
    {
        /* Groups */
        $expectedGroup = $this->objectManager->create(\Magento\Store\Model\Group::class);
        $expectedGroup->setId(123);
        $this->_model->setDefaultGroupId($expectedGroup->getId());
        $this->_model->setGroups([$expectedGroup]);

        $groups = $this->_model->getGroups();
        $this->assertSame($expectedGroup, reset($groups));

        /* Stores */
        $expectedStore = $this->objectManager->create(\Magento\Store\Model\Store::class);
        $expectedStore->setId(456);
        $expectedGroup->setDefaultStoreId($expectedStore->getId());
        $this->_model->setStores([$expectedStore]);

        $stores = $this->_model->getStores();
        $this->assertSame($expectedStore, reset($stores));
    }

    public function testGetGroups()
    {
        $groups = $this->_model->getGroups();
        $this->assertEquals([1], array_keys($groups));
        $this->assertInstanceOf(\Magento\Store\Model\Group::class, $groups[1]);
        $this->assertEquals(1, $groups[1]->getId());
    }

    public function testGetGroupIds()
    {
        $this->assertEquals([1 => 1], $this->_model->getGroupIds());
    }

    public function testGetGroupsCount()
    {
        $this->assertEquals(1, $this->_model->getGroupsCount());
    }

    public function testGetDefaultGroup()
    {
        $defaultGroup = $this->_model->getDefaultGroup();
        $this->assertInstanceOf(\Magento\Store\Model\Group::class, $defaultGroup);
        $this->assertEquals(1, $defaultGroup->getId());

        $this->_model->setDefaultGroupId(null);
        $this->assertFalse($this->_model->getDefaultGroup());
    }

    public function testGetStores()
    {
        $stores = $this->_model->getStores();
        $this->assertEquals([1], array_keys($stores));
        $this->assertInstanceOf(\Magento\Store\Model\Store::class, $stores[1]);
        $this->assertEquals(1, $stores[1]->getId());
    }

    public function testGetStoreIds()
    {
        $this->assertEquals([1 => 1], $this->_model->getStoreIds());
    }

    public function testGetStoreCodes()
    {
        $this->assertEquals([1 => 'default'], $this->_model->getStoreCodes());
    }

    public function testGetStoresCount()
    {
        $this->assertEquals(1, $this->_model->getStoresCount());
    }

    public function testIsCanDelete()
    {
        $this->assertFalse($this->_model->isCanDelete());
        $this->_model->isReadOnly(true);
        $this->assertFalse($this->_model->isCanDelete());
    }

    public function testGetWebsiteGroupStore()
    {
        $this->assertEquals('1--', $this->_model->getWebsiteGroupStore());
        $this->_model->setGroupId(123);
        $this->_model->setStoreId(456);
        $this->assertEquals('1-123-456', $this->_model->getWebsiteGroupStore());
    }

    public function testGetDefaultGroupId()
    {
        $this->assertEquals(1, $this->_model->getDefaultGroupId());
    }

    public function testGetBaseCurrency()
    {
        $currency = $this->_model->getBaseCurrency();
        $this->assertInstanceOf(\Magento\Directory\Model\Currency::class, $currency);
        $this->assertEquals('USD', $currency->getCode());
    }

    public function testGetDefaultStore()
    {
        $defaultStore = $this->_model->getDefaultStore();
        $this->assertInstanceOf(\Magento\Store\Model\Store::class, $defaultStore);
        $this->assertEquals(1, $defaultStore->getId());
    }

    public function testGetDefaultStoresSelect()
    {
        $this->assertInstanceOf(\Magento\Framework\DB\Select::class, $this->_model->getDefaultStoresSelect());
    }

    public function testIsReadonly()
    {
        $this->assertFalse($this->_model->isReadOnly());
        $this->_model->isReadOnly(true);
        $this->assertTrue($this->_model->isReadOnly());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoAppArea adminhtml
     */
    public function testCRUD()
    {
        $this->_model->setData(['code' => 'test_website', 'name' => 'test website', 'default_group_id' => 1]);

        /* emulate admin store */
        $crud = new \Magento\TestFramework\Entity($this->_model, ['name' => 'new name']);
        $crud->testCrud();
    }

    public function testCollection()
    {
        $collection = $this->_model->getCollection()->joinGroupAndStore()->addIdFilter(1);
        $this->assertCount(1, $collection->getItems());
    }

    /**
     * @magentoDataFixture Magento/Store/_files/website.php
     * @magentoCache full_page enabled
     * @magentoDbIsolation disabled
     */
    public function testCacheInvalidationOnWebsiteUpdateAndDeletion()
    {
        $this->typeList->cleanType(Type::TYPE_IDENTIFIER);
        $this->typeList->cleanType(Config::TYPE_IDENTIFIER);

        $this->assertCacheStatusAfterAction(
            $this->typeList->getInvalidated(),
            0,
            'should be clean before website update.'
        );

        $website = $this->objectManager->create(\Magento\Store\Model\Website::class);
        $website->load('test', 'code');
        $website->setName('Test Website 1');
        $website->save();

        $this->assertEquals('Test Website 1', $website->getName());

        $this->assertCacheStatusAfterAction(
            $this->typeList->getInvalidated(),
            1,
            'was not invalidated after website update.'
        );

        /** Marks area as secure to allow website removal */
        $registry = $this->objectManager->get(Registry::class);
        $isSecuredAreaSystemState = $registry->registry('isSecuredArea');
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);

        $website->delete();

        /** Revert mark area secured */
        $registry->unregister('isSecuredArea');
        $registry->register('isSecuredArea', $isSecuredAreaSystemState);

        $this->assertCacheStatusAfterAction(
            $this->typeList->getInvalidated(),
            0,
            'should be clean after website removal.'
        );
    }

    /**
     * @param array $invalidatedCacheTypes
     * @param int $expectedStatus
     * @param string $messageEnd
     * @return void
     */
    private function assertCacheStatusAfterAction(
        array $invalidatedCacheTypes,
        int $expectedStatus,
        string $messageEnd
    ): void {
        if (array_key_exists(Type::TYPE_IDENTIFIER, $invalidatedCacheTypes)) {
            $this->assertEquals(
                $expectedStatus,
                $invalidatedCacheTypes[Type::TYPE_IDENTIFIER]->getData('status'),
                "Full page cache " . $messageEnd
            );
        }

        if (array_key_exists(Config::TYPE_IDENTIFIER, $invalidatedCacheTypes)) {
            $this->assertEquals(
                $expectedStatus,
                $invalidatedCacheTypes[Config::TYPE_IDENTIFIER]->getData('status'),
                "Configuration cache " . $messageEnd
            );
        }
    }
}
