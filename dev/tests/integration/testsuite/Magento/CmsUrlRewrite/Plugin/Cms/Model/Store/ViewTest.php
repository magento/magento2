<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CmsUrlRewrite\Plugin\Cms\Model\Store;

use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use PHPUnit\Framework\TestCase;

/**
 * Test for plugin which is listening store resource model and on save replace cms page url rewrites.
 *
 * @magentoAppArea adminhtml
 */
class ViewTest extends TestCase
{
    /**
     * @var UrlFinderInterface
     */
    private $urlFinder;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Store
     */
    private $storeFactory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->urlFinder = $this->objectManager->create(UrlFinderInterface::class);
        $this->storeFactory = $this->objectManager->create(StoreFactory::class);
    }

    /**
     * Test of replacing cms page url rewrites on create and delete store
     *
     * @magentoDataFixture Magento/Cms/_files/two_cms_page_with_same_url_for_different_stores.php
     * @magentoDataFixture Magento/Cms/_files/pages.php
     *
     * @return void
     */
    public function testUrlRewritesChangesAfterStoreSave(): void
    {
        $storeId = $this->createStore();
        $this->assertUrlRewritesCount($storeId, 'page100', 1);
        $this->assertUrlRewritesCount($storeId, 'page1', 0);
        $this->deleteStore($storeId);
        $this->assertUrlRewritesCount($storeId, 'page100', 0);
    }

    /**
     * Assert url rewrites count by store id and request path
     *
     * @param int $storeId
     * @param string $requestPath
     * @param int $expectedCount
     * @return void
     */
    private function assertUrlRewritesCount(int $storeId, string $requestPath, int $expectedCount): void
    {
        $data = [
            UrlRewrite::REQUEST_PATH => $requestPath,
            UrlRewrite::STORE_ID => $storeId
        ];
        $urlRewrites = $this->urlFinder->findAllByData($data);
        $this->assertCount($expectedCount, $urlRewrites);
    }

    /**
     * Create test store
     *
     * @return int
     */
    private function createStore(): int
    {
        $store = $this->storeFactory->create();
        $store->setCode('test_' . random_int(0, 999))
            ->setName('Test Store')
            ->unsId()
            ->save();

        return (int)$store->getId();
    }

    /**
     * Delete test store
     *
     * @param int $storeId
     * @return void
     */
    private function deleteStore(int $storeId): void
    {
        $store = $this->storeFactory->create();
        $store->load($storeId);
        if ($store !== null) {
            $store->delete();
        }
    }
}
