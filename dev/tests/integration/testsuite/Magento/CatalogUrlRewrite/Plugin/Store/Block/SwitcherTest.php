<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Plugin\Store\Block;

/**
 * Integration tests for Magento\CatalogUrlRewrite\Plugin\Store\Block\Switcher block.
 */
class SwitcherTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Store\Block\Switcher
     */
    private $model;

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $this->objectManager->create(\Magento\Store\Block\Switcher::class);
        $this->storeRepository = $this->objectManager->create(\Magento\Store\Api\StoreRepositoryInterface::class);
    }

    /**
     * Test that after switching from Store 1 to Store 2 with another root Category user gets correct store url.
     *
     * @magentoDataFixture Magento/Store/_files/store.php
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/two_categories_per_two_store_groups.php
     * @magentoAppArea frontend
     * @return void
     */
    public function testGetTargetStorePostData(): void
    {
        $storeCode = 'test';
        $store = $this->storeRepository->get($storeCode);
        $result = json_decode($this->model->getTargetStorePostData($store), true);
        
        $this->assertContains($storeCode, $result['action']);
    }
}
