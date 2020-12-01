<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:disable Magento2.Security.Superglobal
 */
class CreateStoreTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

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

        $this->createStoreModel = $this->objectManager->get(
            \Magento\Store\Model\CreateStore::class
        );

        $this->websiteModel = $this->objectManager->create(
            \Magento\Store\Model\Website::class
        );
        $this->websiteModel->load(1);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->objectManager = null;
        $this->createStoreModel = null;
        $this->websiteModel = null;
    }

    /**
     * @param $code
     * @param $data
     * @dataProvider loadCreateDataProvider
     */
    public function testExecute($code, $data)
    {
        $defaultGroupId = $this->websiteModel->getDefaultGroupId();
        $data['group_id'] = $defaultGroupId;

        $store = $this->createStoreModel->execute($data);
        $this->assertInstanceOf(\Magento\Store\Api\Data\StoreInterface::class, $store);
        $this->assertNotNull($store->getId(), 'Store was not saved correctly');
        $this->assertEquals($code, $store->getCode());

        /** @var \Magento\Framework\Registry $registry */
        $registry = $this->objectManager->get(\Magento\Framework\Registry::class);

        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);

        $store->delete();
    }

    /**
     * @return array
     */
    public function loadCreateDataProvider()
    {
        return [['code',
            [
                'name' => 'code',
                'code' => 'code',
                'is_active'  => 0,
                'sort_order' => 10,
            ]
        ]];
    }
}
