<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Model\Category\Plugin\Store;

use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use PHPUnit\Framework\TestCase;

/**
 * Verify generate url rewrites after creating store view.
 */
class ViewTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var StoreFactory
     */
    private $storeFactory;

    /**
     * @var UrlRewriteCollectionFactory
     */
    private $urlRewriteCollectionFactory;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->storeFactory = $this->objectManager->create(StoreFactory::class);
        $this->urlRewriteCollectionFactory = $this->objectManager->get(UrlRewriteCollectionFactory::class);
    }

    /**
     * Verify that url will be generated for category which excluded for menu after creating store view
     *
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/category_excluded_from_menu.php
     *
     * @return void
     */
    public function testAfterSaveVerifyCategoryExcludedForMenuUrlRewrite(): void
    {
        $website = $this->objectManager->get(StoreManagerInterface::class)
            ->getWebsite();

        $store = $this->storeFactory->create();
        $store->setCode('custom_store_777')
            ->setWebsiteId($website->getId())
            ->setGroupId($website->getDefaultGroupId())
            ->setName('Custom Test Store')
            ->setSortOrder(10)
            ->setIsActive(1)
            ->save();

        $urlRewriteCollection = $this->urlRewriteCollectionFactory->create();
        $urlRewriteCollection->addFieldToFilter(UrlRewrite::STORE_ID, $store->getId())
            ->addFieldToFilter(UrlRewrite::TARGET_PATH, 'catalog/category/view/id/' . 3);

        $this->assertCount(1, $urlRewriteCollection);
    }
}
