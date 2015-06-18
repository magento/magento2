<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Model\Resource;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Ui\Api\BookmarkRepositoryInterface;
use Magento\Ui\Api\Data\BookmarkInterface;

/**
 * @magentoAppIsolation enabled
 * @magentoAppArea adminhtml
 */
class BookmarkRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BookmarkRepositoryInterface
     */
    protected $bookmarkRepository;

    /**
     * @var \Magento\Ui\Model\BookmarkFactory
     */
    protected $bookmarkFactory;

    /**
     * @var BookmarkInterface
     */
    protected $bookmark;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    protected function setUp()
    {
        $this->bookmarkRepository = Bootstrap::getObjectManager()
            ->create('Magento\Ui\Model\Resource\BookmarkRepository');
        $this->bookmarkFactory = Bootstrap::getObjectManager()->create('Magento\Ui\Model\BookmarkFactory');

        /** @var $customerRepository \Magento\Customer\Api\CustomerRepositoryInterface */
        $this->customerRepository = Bootstrap::getObjectManager()
            ->create('Magento\Customer\Api\CustomerRepositoryInterface');
        $newCustomerEntity = Bootstrap::getObjectManager()
            ->create('Magento\Customer\Api\Data\CustomerInterfaceFactory')
            ->create()
            ->setStoreId(1)
            ->setWebsiteId(1)
            ->setEmail('bookmark_user@example.com')
            ->setFirstname('TestFn')
            ->setLastname('TestLn')
            ->setGroupId(1);
        $newCustomerEntity = $this->customerRepository->save($newCustomerEntity);

        $this->bookmark = $this->bookmarkFactory->create()->setUserId($newCustomerEntity->getId())->setTitle('test');
        $this->bookmark = $this->bookmarkRepository->save($this->bookmark);
    }

    protected function tearDown()
    {
        $this->bookmarkRepository->delete($this->bookmark);
        $this->customerRepository->delete($this->customerRepository->get('bookmark_user@example.com'));
    }

    public function testGetList()
    {
        /** @var \Magento\Framework\Api\SearchCriteriaBuilder $searchBuilder */
        $searchBuilder = Bootstrap::getObjectManager()->create('Magento\Framework\Api\SearchCriteriaBuilder');
        $searchResult = $this->bookmarkRepository->getList($searchBuilder->create());
        $this->assertTrue($searchResult->getTotalCount() > 0);
    }
}
