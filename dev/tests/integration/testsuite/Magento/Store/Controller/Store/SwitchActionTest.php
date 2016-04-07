<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Controller\Store;

/**
 * Test for store switch controller.
 */
class SwitchActionTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * Ensure that proper default store code is calculated.
     *
     * Make sure that if default store code is changed from 'default' to something else,
     * proper code is used in HTTP context. If default store code is still 'default' this may lead to
     * incorrect work of page cache.
     *
     * @magentoDbIsolation enabled
     */
    public function testExecuteWithCustomDefaultStore()
    {

        $defaultStoreCode = 'default';
        $modifiedDefaultCode = 'modified_default_code';
        $this->changeStoreCode($defaultStoreCode, $modifiedDefaultCode);

        $this->dispatch('stores/store/switch');
        /** @var \Magento\Framework\App\Http\Context $httpContext */
        $httpContext = $this->_objectManager->get('Magento\Framework\App\Http\Context');
        $httpContext->unsValue(\Magento\Store\Model\Store::ENTITY);
        $this->assertEquals($modifiedDefaultCode, $httpContext->getValue(\Magento\Store\Model\Store::ENTITY));

        $this->changeStoreCode($modifiedDefaultCode, $defaultStoreCode);
    }

    /**
     * Change store code.
     *
     * @param string $from
     * @param string $to
     */
    protected function changeStoreCode($from, $to)
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->_objectManager->create('Magento\Store\Model\Store');
        $store->load($from, 'code');
        $store->setCode($to);
        $store->save();
        /** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
        $storeManager = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $storeManager->reinitStores();
    }
}
