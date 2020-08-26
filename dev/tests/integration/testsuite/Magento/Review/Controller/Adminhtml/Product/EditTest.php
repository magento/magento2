<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Controller\Adminhtml\Product;

use Magento\Review\Model\Review;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Magento\Framework\Acl\Builder;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\UrlInterface;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory;

/**
 * Test Edit action.
 *
 * @magentoAppArea adminhtml
 */
class EditTest extends AbstractBackendController
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Builder
     */
    private $aclBuilder;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->aclBuilder = $this->objectManager->get(Builder::class);
        $this->urlBuilder = $this->objectManager->get(UrlInterface::class);
        $this->collectionFactory = $this->objectManager->get(CollectionFactory::class);
        $this->aclBuilder->resetRuntimeAcl();
    }

    /**
     * Tests Edit action without reviews_all resource when manipulating Pending review.
     *
     * @return void
     * @magentoDataFixture Magento/Review/_files/reviews.php
     */
    public function testAclHasAccess(): void
    {
        $collection = $this->collectionFactory->create();
        $collection->addFilter('detail.nickname', 'Nickname');
        /** @var Review $review */
        $review = $collection->getItemByColumnValue('status_id', Review::STATUS_PENDING);

        // Exclude resource from ACL.
        $this->aclBuilder->getAcl()->deny(null, 'Magento_Review::reviews_all');
        $this->uri = 'backend/review/product/edit/id/' . $review->getId();

        parent::testAclHasAccess();
    }

    /**
     * Tests Edit action without pending and reviews_all resources.
     *
     * @return void
     */
    public function testAclNoAccess(): void
    {
        // Exclude resource from ACL.
        $this->resource = ['Magento_Review::reviews_all', 'Magento_Review::pending'];
        $this->uri = 'backend/review/product/edit/id/' . 'doesn\'t matter';

        parent::testAclNoAccess();
    }
}
