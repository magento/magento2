<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CmsUrlRewrite\Plugin\Cms\Model\Store;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Test for plugin which is listening store resource model and on save replace cms page url rewrites
 *
 * @magentoAppArea adminhtml
 */
class ViewTest extends \PHPUnit\Framework\TestCase
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
     * @var StoreFactory
     */
    private $storeFactory;

    /**
     * @var string
     */
    private $storeCode;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->urlFinder = $this->objectManager->create(UrlFinderInterface::class);
        $this->storeFactory = $this->objectManager->create(StoreFactory::class);
        $this->websiteRepository = $this->objectManager->get(WebsiteRepositoryInterface::class);
        $this->storeCode = 'test_' . mt_rand();
    }

    /**
     * Test of replacing cms page url rewrites on create and delete store
     *
     * @magentoDataFixture Magento/Cms/_files/pages.php
     */
    public function testUrlRewritesChangesAfterStoreSave()
    {
        $data = [
            UrlRewrite::REQUEST_PATH => 'page100',
        ];
        $urlRewrites = $this->urlFinder->findAllByData($data);
        $this->assertCount(1, $urlRewrites);
        $this->createStore();
        $urlRewrites = $this->urlFinder->findAllByData($data);
        $this->assertCount(2, $urlRewrites);
        $this->deleteStore();
        $urlRewrites = $this->urlFinder->findAllByData($data);
        $this->assertCount(1, $urlRewrites);
    }

    /**
     * Create test store
     *
     * @return void
     */
    private function createStore(): void
    {
        /** @var $store Store */
        $store = $this->storeFactory->create();
        if (!$store->load($this->storeCode, 'code')->getId()) {
            $store->setData(
                [
                    'code' => $this->storeCode,
                    'website_id' => $this->websiteRepository->getDefault()->getId(),
                    'group_id' => $this->websiteRepository->getDefault()->getDefaultGroupId(),
                    'name' => 'Test Store',
                    'sort_order' => '0',
                    'is_active' => '1',
                ]
            );
            $store->save();
        } else {
            if ($store->getId()) {
                /** @var \Magento\TestFramework\Helper\Bootstrap $registry */
                $registry = $this->objectManager->get(
                    Registry::class
                );
                $registry->unregister('isSecureArea');
                $registry->register('isSecureArea', true);
                $store->delete();
                $registry->unregister('isSecureArea');
                $registry->register('isSecureArea', false);
                $store = $this->objectManager->create(Store::class);
                $store->setData(
                    [
                        'code' => $this->storeCode,
                        'website_id' => $this->websiteRepository->getDefault()->getId(),
                        'group_id' => $this->websiteRepository->getDefault()->getDefaultGroupId(),
                        'name' => 'Test Store',
                        'sort_order' => '0',
                        'is_active' => '1',
                    ]
                );
                $store->save();
            }
        }
    }

    /**
     * Delete test store
     *
     * @return void
     */
    private function deleteStore(): void
    {
        /** @var Registry $registry */
        $registry = $this->objectManager->get(Registry::class);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);
        /** @var Store $store */
        $store = $this->objectManager->get(Store::class);
        $store->load($this->storeCode, 'code');
        if ($store->getId()) {
            $store->delete();
        }
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
    }
}
