<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Block;

/**
 * Integration tests for \Magento\Store\Block\Switcher block.
 */
class SwitcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $_objectManager;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * Test that GetTargetStorePostData() method return correct store URL.
     *
     * @magentoDataFixture Magento/Store/_files/store.php
     * @return void
     */
    public function testGetTargetStorePostData()
    {
        $storeCode = 'test';
        /** @var \Magento\Store\Block\Switcher $block */
        $block = $this->_objectManager->create(\Magento\Store\Block\Switcher::class);
        /** @var \Magento\Store\Api\StoreRepositoryInterface $storeRepository */
        $storeRepository = $this->_objectManager->create(\Magento\Store\Api\StoreRepositoryInterface::class);
        $store = $storeRepository->get($storeCode);
        $result = json_decode($block->getTargetStorePostData($store), true);
        
        $this->assertContains($storeCode, $result['action']);
    }
}
