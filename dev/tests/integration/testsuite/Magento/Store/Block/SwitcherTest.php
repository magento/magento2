<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Block;

use Magento\Framework\App\ActionInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Url\DecoderInterface;
use Magento\Framework\App\ScopeInterface;

/**
 * Integration tests for \Magento\Store\Block\Switcher block.
 */
class SwitcherTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $_objectManager;

    /**
     * @var DecoderInterface
     */
    private $decoder;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->_objectManager = Bootstrap::getObjectManager();
        $this->decoder = Bootstrap::getObjectManager()->create(DecoderInterface::class);
    }

    /**
     * Test that GetTargetStorePostData() method returns correct data.
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
        $url = parse_url($this->decoder->decode($result['data'][ActionInterface::PARAM_NAME_URL_ENCODED]));
        $storeParsedQuery = [];
        if (isset($url['query'])) {
            parse_str($url['query'], $storeParsedQuery);
        }

        $this->assertSame($storeCode, $result['data']['___store']);
        $this->assertSame($storeCode, $storeParsedQuery['___store']);
        $this->assertSame(ScopeInterface::SCOPE_DEFAULT, $result['data']['___from_store']);
    }
}
