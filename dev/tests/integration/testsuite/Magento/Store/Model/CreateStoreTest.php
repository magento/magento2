<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model;

class CreateStoreTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Store\Api\Data\StoreInterface
     */
    protected $storeModel;

    /**
     * @var \Magento\Store\Model\CreateStore
     */
    protected $createStoreModel;

    /**
     * @var \Magento\Store\Model\Website
     */
    protected $websiteModel;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->storeModel = $this->objectManager->get(
            \Magento\Store\Model\Store::class
        );

        $this->createStoreModel = $this->objectManager->get(
            \Magento\Store\Model\CreateStore::class
        );

        $this->websiteModel = $this->objectManager->create(
            \Magento\Store\Model\Website::class
        );
        $this->websiteModel->load(1);
    }

    /**
     * @param $data
     * @dataProvider loadCreateDataProvider
     */
    public function testExecute($data)
    {
        $defaultGroupId = $this->websiteModel->getDefaultGroupId();
        $data['group_id'] = $defaultGroupId;

        $store = $this->storeModel->setData($data);
        $store = $this->createStoreModel->execute($store);
        $this->assertInstanceOf(\Magento\Store\Api\Data\StoreInterface::class, $store);
        $this->assertNotNull($store->getId(), 'Store was not saved correctly');
        $this->assertEquals($data['code'], $store->getCode());

        /** @var \Magento\Framework\Registry $registry */
        $registry = $this->objectManager->get(\Magento\Framework\Registry::class);

        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);

        $store->delete();

        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
    }

    /**
     * @return array
     */
    public function loadCreateDataProvider()
    {
        return [[
            [
                'name' => 'code',
                'code' => 'code',
                'is_active'  => 0,
                'sort_order' => 10,
            ]
        ]];
    }
}
