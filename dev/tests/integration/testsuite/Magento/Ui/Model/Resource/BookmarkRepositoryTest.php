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
 * @magentoDataFixture Magento/Customer/_files/customer.php
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

    protected function setUp()
    {
        $this->bookmarkRepository = Bootstrap::getObjectManager()
            ->create('Magento\Ui\Model\Resource\BookmarkRepository');
        $this->bookmarkFactory = Bootstrap::getObjectManager()->create('Magento\Ui\Model\BookmarkFactory');
        /** @var $customerRepository \Magento\Customer\Api\CustomerRepositoryInterface */
        $customerRepository = Bootstrap::getObjectManager()
            ->create('Magento\Customer\Api\CustomerRepositoryInterface');
        $customerId = $customerRepository->get('customer@example.com')->getId();
        $this->bookmark = $this->bookmarkFactory->create()->setUserId($customerId)->setTitle('test');
        $this->bookmark = $this->bookmarkRepository->save($this->bookmark);
    }

    protected function tearDown()
    {
        $this->bookmarkRepository->delete($this->bookmark);
    }

    public function testGetList()
    {
        /** @var \Magento\Framework\Api\SearchCriteriaBuilder $searchBuilder */
        $searchBuilder = Bootstrap::getObjectManager()->create('Magento\Framework\Api\SearchCriteriaBuilder');
        $searchResult = $this->bookmarkRepository->getList($searchBuilder->create());
        $this->assertTrue($searchResult->getTotalCount() > 0);
    }
}
