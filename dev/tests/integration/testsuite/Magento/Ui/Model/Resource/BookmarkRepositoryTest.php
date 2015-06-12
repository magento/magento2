<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Model\Resource;

use Magento\TestFramework\Helper\Bootstrap;

class BookmarkRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BookmarkRepository
     */
    protected $bookmarkRepository;

    protected function setUp()
    {
        $this->bookmarkRepository = Bootstrap::getObjectManager()
            ->create('Magento\Ui\Model\Resource\BookmarkRepository');
    }

    public function testGetListEmpty()
    {
        /** @var \Magento\Framework\Api\SearchCriteriaBuilder $searchBuilder */
        $searchBuilder = Bootstrap::getObjectManager()->create('Magento\Framework\Api\SearchCriteriaBuilder');
        $searchResults = $this->bookmarkRepository->getList($searchBuilder->create());
        $this->assertEquals(0, $searchResults->getTotalCount());
    }
}
